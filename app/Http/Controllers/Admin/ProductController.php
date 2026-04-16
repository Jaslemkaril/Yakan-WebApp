<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Services\CloudinaryService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use App\Models\ProductBundleItem;
use App\Models\ProductVariant;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource with search and filter.
     */
    public function index(Request $request)
    {
        $bundleFeatureEnabled = $this->bundleFeatureEnabled();

        $query = Product::with('category');
        if ($bundleFeatureEnabled) {
            $query->withCount('bundleItems');
        }

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('description', 'like', $term)
                  ->orWhere('sku', 'like', $term);
            });
        }

        // Status filter — only apply if explicitly set
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Category filter
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('name', $request->category);
            });
        }

        // Stock level filter — use inventory.quantity when available, fall back to products.stock
        if ($request->filled('stock')) {
            $query->leftJoin('inventory', 'inventory.product_id', '=', 'products.id')
                  ->select('products.*');
            switch ($request->stock) {
                case 'in_stock':
                    $query->whereRaw('COALESCE(inventory.quantity, products.stock) > 10');
                    break;
                case 'low_stock':
                    $query->whereRaw('COALESCE(inventory.quantity, products.stock) > 0')
                          ->whereRaw('COALESCE(inventory.quantity, products.stock) <= 10');
                    break;
                case 'out_of_stock':
                    $query->whereRaw('COALESCE(inventory.quantity, products.stock) <= 0');
                    break;
            }
        }

        $products = $query->latest('products.created_at')->paginate(10)->withQueryString();

        $categories = \App\Models\Category::orderBy('name')->get();

        // Counts always come from all products (unfiltered) so stat cards are always accurate
        $allProductsCount  = Product::count();
        $activeCount       = Product::where('status', 'active')->count();
        $lowStockCount     = Product::leftJoin('inventory', 'inventory.product_id', '=', 'products.id')
            ->whereRaw('COALESCE(inventory.quantity, products.stock) > 0')
            ->whereRaw('COALESCE(inventory.quantity, products.stock) <= 10')
            ->count('products.id');
        $outOfStockCount   = Product::leftJoin('inventory', 'inventory.product_id', '=', 'products.id')
            ->whereRaw('COALESCE(inventory.quantity, products.stock) <= 0')
            ->count('products.id');

        return view('admin.products.index', compact('products', 'categories', 'allProductsCount', 'activeCount', 'lowStockCount', 'outOfStockCount', 'bundleFeatureEnabled'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->ensureProductDiscountColumnsExist();

        $bundleFeatureEnabled = $this->bundleFeatureEnabled();
        $categories = \App\Models\Category::orderBy('name')->get();
        $bundleComponents = $bundleFeatureEnabled
            ? Product::orderBy('name')->get(['id', 'name', 'price', 'stock'])
            : collect();
        $initialVariantRows = old('variant_rows', []);

        return view('admin.products.create', compact('categories', 'bundleComponents', 'bundleFeatureEnabled', 'initialVariantRows'));
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        $this->ensureProductDiscountColumnsExist();

        // Validate input
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'stock' => 'required|integer|min:0',
            'discount_type' => 'nullable|in:percent,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'discount_starts_at' => 'nullable|date',
            'discount_ends_at' => 'nullable|date|after_or_equal:discount_starts_at',
            'description' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'image_colors' => 'nullable|array',
            'image_colors.*' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'category_id' => 'nullable|exists:categories,id',
            'available_sizes' => 'nullable|json',
            'available_colors' => 'nullable|json',
            'is_bundle' => 'nullable|boolean',
            'bundle_items' => 'nullable|array',
            'bundle_items.*.product_id' => 'nullable|integer|exists:products,id',
            'bundle_items.*.quantity' => 'nullable|integer|min:1',
            'variant_rows' => 'nullable|array',
            'variant_rows.*.sku' => 'nullable|string|max:100',
            'variant_rows.*.size' => 'nullable|string|max:50',
            'variant_rows.*.color' => 'nullable|string|max:50',
            'variant_rows.*.price' => 'nullable|numeric|min:0',
            'variant_rows.*.stock' => 'nullable|integer|min:0',
            'variant_rows.*.is_active' => 'nullable|boolean',
        ]);

        $bundleFeatureEnabled = $this->bundleFeatureEnabled();
        $isBundle = $bundleFeatureEnabled && $request->boolean('is_bundle');
        $bundleItems = $bundleFeatureEnabled
            ? $this->sanitizeBundleItems($request->input('bundle_items', []))
            : [];
        $this->validateBundleItems($bundleItems, $isBundle);

        $variantRows = $this->sanitizeVariantRows($request->input('variant_rows', []));
        $this->validateVariantRows($variantRows);

        // Handle multiple image uploads with color associations
        $imagePath = null;
        $allImages = [];
        $imageColors = $request->input('image_colors', []);
        $cloudinary = new CloudinaryService();
        
        if ($request->hasFile('images')) {
            $imageIndex = 0;
            
            foreach ($request->file('images') as $index => $image) {
                if ($image && $image->isValid()) {
                    $storedPath = null;
                    
                    // Try Cloudinary first (production)
                    if ($cloudinary->isEnabled()) {
                        $result = $cloudinary->uploadFile($image, 'products');
                        if ($result) {
                            $storedPath = $result['url'];
                        }
                    }
                    
                    // Fallback to local storage
                    if (!$storedPath) {
                        $uploadDir = public_path('uploads/products');
                        if (!is_dir($uploadDir)) {
                            @mkdir($uploadDir, 0777, true);
                        }
                        $imageName = time() . '_' . $imageIndex . '_' . $image->getClientOriginalName();
                        $image->move($uploadDir, $imageName);
                        $storedPath = $imageName;
                    }
                    
                    $allImages[] = [
                        'path' => $storedPath,
                        'color' => $imageColors[$index] ?? null,
                        'sort_order' => $imageIndex
                    ];
                    
                    // First VALID image becomes the main image
                    if ($imagePath === null) {
                        $imagePath = $storedPath;
                    }
                    
                    $imageIndex++;
                }
            }
        }

        // Parse sizes and colors JSON (kept for backward compatibility if no variants are defined)
        $sizes = $request->available_sizes ? json_decode($request->available_sizes, true) : null;
        $colors = $request->available_colors ? json_decode($request->available_colors, true) : null;

        $resolvedPrice = (float) $request->price;
        $resolvedStock = (int) $request->stock;

        if (!empty($variantRows)) {
            $resolvedPrice = (float) collect($variantRows)->min('price');
            $resolvedStock = (int) collect($variantRows)->sum('stock');

            $sizes = collect($variantRows)
                ->pluck('size')
                ->filter(fn($value) => !empty($value))
                ->unique()
                ->values()
                ->all();

            $colors = collect($variantRows)
                ->pluck('color')
                ->filter(fn($value) => !empty($value))
                ->unique()
                ->values()
                ->all();
        }

        $supportsProductDiscounts = Schema::hasColumns('products', ['discount_type', 'discount_value', 'discount_starts_at', 'discount_ends_at']);
        $discountPayload = $supportsProductDiscounts
            ? $this->normalizeProductDiscountInput($request, $resolvedPrice)
            : [
                'discount_type' => null,
                'discount_value' => null,
                'discount_starts_at' => null,
                'discount_ends_at' => null,
            ];

        // Create product
        $createPayload = [
            'name' => $request->name,
            'price' => $resolvedPrice,
            'stock' => $resolvedStock,
            'description' => $request->description,
            'image' => $imagePath,
            'status' => $request->status,
            'category_id' => $request->category_id,
            'available_sizes' => $sizes,
            'available_colors' => $colors,
        ];

        if ($supportsProductDiscounts) {
            $createPayload['discount_type'] = $discountPayload['discount_type'];
            $createPayload['discount_value'] = $discountPayload['discount_value'];
            $createPayload['discount_starts_at'] = $discountPayload['discount_starts_at'];
            $createPayload['discount_ends_at'] = $discountPayload['discount_ends_at'];
        }

        $product = Product::create($createPayload);
        
        // Store all images with color associations in JSON column
        if (!empty($allImages)) {
            $product->update(['all_images' => json_encode($allImages)]);
        }

        $this->syncVariantRows($product, $variantRows);
        $this->syncBundleItems($product, $bundleItems, $isBundle);

        // Ensure session is saved before redirect
        $request->session()->save();
        
        \Log::info('Product created successfully', [
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'session_id' => $request->session()->getId()
        ]);

        // Get auth_token if present to append to redirect
        $authToken = $request->input('auth_token') ?? $request->attributes->get('admin_auth_token');
        $redirectUrl = route('admin.products.index');
        if ($authToken) {
            $redirectUrl .= '?auth_token=' . $authToken;
        }
        
        // Clear product API cache so mobile app sees the new product immediately
        Cache::flush();

        $successMessage = $isBundle
            ? 'Bundle created successfully with ' . count($bundleItems) . ' item(s).'
            : 'Product created successfully with ' . count($allImages) . ' image(s).';

        return redirect($redirectUrl)->with('success', $successMessage);
    }

    /**
     * Display the specified product.
     */
    public function show(string $id)
    {
        $product = Product::with('inventory')->findOrFail($id);
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(string $id)
    {
        $this->ensureProductDiscountColumnsExist();

        $bundleFeatureEnabled = $this->bundleFeatureEnabled();

        $productQuery = Product::with('category', 'variants');
        if ($bundleFeatureEnabled) {
            $productQuery->with(['bundleItems.componentProduct']);
        }

        $product = $productQuery->findOrFail($id);
        $categories = \App\Models\Category::orderBy('name')->get();
        $bundleComponents = $bundleFeatureEnabled
            ? Product::where('id', '!=', $product->id)
                ->orderBy('name')
                ->get(['id', 'name', 'price', 'stock'])
            : collect();
        $existingBundleItems = $bundleFeatureEnabled
            ? $product->bundleItems
            : collect();
        $existingVariantRows = $product->variants
            ->map(function (ProductVariant $variant) {
                return [
                    'sku' => $variant->sku,
                    'size' => $variant->size,
                    'color' => $variant->color,
                    'price' => (float) $variant->price,
                    'stock' => (int) $variant->stock,
                    'is_active' => (bool) $variant->is_active,
                ];
            })
            ->values()
            ->all();

        // Stock logs grouped for display (guard against missing table during migration)
        $stockLogs   = collect();
        $today       = 0;
        $thisWeek    = 0;
        $thisYear    = 0;
        $overall     = 0;
        $recentLogs  = collect();

        if (\Schema::hasTable('stock_logs')) {
            $stockLogs  = \App\Models\StockLog::with('creator:id,name')
                ->where('product_id', $product->id)
                ->orderBy('created_at', 'desc')
                ->take(100)
                ->get();

            $today      = $stockLogs->filter(fn($l) => $l->quantity > 0 && $l->created_at->isToday())->sum('quantity');
            $thisWeek   = $stockLogs->filter(fn($l) => $l->quantity > 0 && $l->created_at->isSameWeek(now()))->sum('quantity');
            $thisYear   = $stockLogs->filter(fn($l) => $l->quantity > 0 && $l->created_at->isSameYear(now()))->sum('quantity');
            $overall    = $stockLogs->filter(fn($l) => $l->quantity > 0)->sum('quantity');
            $recentLogs = $stockLogs->take(15);
        }

        return view('admin.products.edit', compact('product', 'categories', 'bundleComponents', 'existingBundleItems', 'existingVariantRows', 'bundleFeatureEnabled', 'stockLogs', 'today', 'thisWeek', 'thisYear', 'overall', 'recentLogs'));
    }


    /**
     * Update the specified product.
     */
    public function update(Request $request, string $id)
    {
        $this->ensureProductDiscountColumnsExist();

        $product = Product::findOrFail($id);

        // Validate input (stock is managed via Stock In button, not this form)
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'discount_type' => 'nullable|in:percent,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'discount_starts_at' => 'nullable|date',
            'discount_ends_at' => 'nullable|date|after_or_equal:discount_starts_at',
            'description' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'image_colors' => 'nullable|array',
            'image_colors.*' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'category_id' => 'nullable|exists:categories,id',
            'available_sizes' => 'nullable|json',
            'available_colors' => 'nullable|json',
            'delete_images' => 'nullable|json',
            'is_bundle' => 'nullable|boolean',
            'bundle_items' => 'nullable|array',
            'bundle_items.*.product_id' => 'nullable|integer|exists:products,id',
            'bundle_items.*.quantity' => 'nullable|integer|min:1',
            'variant_rows' => 'nullable|array',
            'variant_rows.*.sku' => 'nullable|string|max:100',
            'variant_rows.*.size' => 'nullable|string|max:50',
            'variant_rows.*.color' => 'nullable|string|max:50',
            'variant_rows.*.price' => 'nullable|numeric|min:0',
            'variant_rows.*.stock' => 'nullable|integer|min:0',
            'variant_rows.*.is_active' => 'nullable|boolean',
        ]);

        $bundleFeatureEnabled = $this->bundleFeatureEnabled();
        $isBundle = $bundleFeatureEnabled && $request->boolean('is_bundle');
        $bundleItems = $bundleFeatureEnabled
            ? $this->sanitizeBundleItems($request->input('bundle_items', []))
            : [];
        $this->validateBundleItems($bundleItems, $isBundle, $product->id);

        $variantRows = $this->sanitizeVariantRows($request->input('variant_rows', []));
        $this->validateVariantRows($variantRows);

        // Handle image deletions
        $allImages = $product->all_images ?? [];
        // Ensure $allImages is an array
        if (is_string($allImages)) {
            $allImages = json_decode($allImages, true) ?? [];
        }
        
        $imagesToDelete = $request->delete_images ? json_decode($request->delete_images, true) : [];
        $cloudinary = new CloudinaryService();
        
        if (!empty($imagesToDelete)) {
            // Remove deleted images from array and delete files
            $allImages = array_filter($allImages, function($img) use ($imagesToDelete, $cloudinary) {
                if (in_array($img['path'], $imagesToDelete)) {
                    // Delete from Cloudinary or local
                    if (str_contains($img['path'], 'cloudinary.com')) {
                        // Extract public_id from Cloudinary URL for deletion
                        if (preg_match('/\/upload\/(?:v\d+\/)?(.+?)(?:\.[a-z]+)?$/', $img['path'], $matches)) {
                            $cloudinary->delete($matches[1]);
                        }
                    } else {
                        $filePath = public_path('uploads/products/' . $img['path']);
                        if (file_exists($filePath)) {
                            @unlink($filePath);
                        }
                    }
                    return false;
                }
                return true;
            });
            // Reindex array
            $allImages = array_values($allImages);
        }

        // Handle new image uploads with color associations
        $imagePath = $product->image;
        $imageColors = $request->input('image_colors', []);
        $firstNewImage = null;
        
        if ($request->hasFile('images')) {
            $imageIndex = count($allImages);
            
            foreach ($request->file('images') as $index => $image) {
                if ($image && $image->isValid()) {
                    $storedPath = null;
                    
                    // Try Cloudinary first (production)
                    if ($cloudinary->isEnabled()) {
                        $result = $cloudinary->uploadFile($image, 'products');
                        if ($result) {
                            $storedPath = $result['url'];
                        }
                    }
                    
                    // Fallback to local storage
                    if (!$storedPath) {
                        $uploadDir = public_path('uploads/products');
                        if (!is_dir($uploadDir)) {
                            @mkdir($uploadDir, 0777, true);
                        }
                        $imageName = time() . '_' . $imageIndex . '_' . $image->getClientOriginalName();
                        $image->move($uploadDir, $imageName);
                        $storedPath = $imageName;
                    }
                    
                    $allImages[] = [
                        'path' => $storedPath,
                        'color' => $imageColors[$index] ?? null,
                        'sort_order' => $imageIndex
                    ];
                    
                    // Track first new image
                    if ($firstNewImage === null) {
                        $firstNewImage = $storedPath;
                    }
                    
                    $imageIndex++;
                }
            }
            
            // Update main image to first new image if new images were uploaded
            if ($firstNewImage !== null) {
                $imagePath = $firstNewImage;
            }
        }
        
        // If main image was deleted and no new images, set first remaining image as main
        if (!empty($allImages) && (in_array($imagePath, $imagesToDelete) || !$imagePath)) {
            $imagePath = $allImages[0]['path'];
        }

        // Parse sizes and colors JSON (kept for backward compatibility if no variants are defined)
        $sizes = $request->available_sizes ? json_decode($request->available_sizes, true) : null;
        $colors = $request->available_colors ? json_decode($request->available_colors, true) : null;

        $resolvedPrice = (float) $request->price;
        $resolvedStock = (int) $product->stock;

        if (!empty($variantRows)) {
            $resolvedPrice = (float) collect($variantRows)->min('price');
            $resolvedStock = (int) collect($variantRows)->sum('stock');

            $sizes = collect($variantRows)
                ->pluck('size')
                ->filter(fn($value) => !empty($value))
                ->unique()
                ->values()
                ->all();

            $colors = collect($variantRows)
                ->pluck('color')
                ->filter(fn($value) => !empty($value))
                ->unique()
                ->values()
                ->all();
        }

        $supportsProductDiscounts = Schema::hasColumns('products', ['discount_type', 'discount_value', 'discount_starts_at', 'discount_ends_at']);
        $discountPayload = $supportsProductDiscounts
            ? $this->normalizeProductDiscountInput($request, $resolvedPrice)
            : [
                'discount_type' => null,
                'discount_value' => null,
                'discount_starts_at' => null,
                'discount_ends_at' => null,
            ];

        // Update product (stock managed via Stock In, not edit form)
        $updatePayload = [
            'name' => $request->name,
            'price' => $resolvedPrice,
            'stock' => $resolvedStock,
            'description' => $request->description,
            'status' => $request->status,
            'category_id' => $request->category_id,
            'image' => $imagePath,
            'available_sizes' => $sizes,
            'available_colors' => $colors,
            'all_images' => !empty($allImages) ? json_encode($allImages) : null,
        ];

        if ($supportsProductDiscounts) {
            $updatePayload['discount_type'] = $discountPayload['discount_type'];
            $updatePayload['discount_value'] = $discountPayload['discount_value'];
            $updatePayload['discount_starts_at'] = $discountPayload['discount_starts_at'];
            $updatePayload['discount_ends_at'] = $discountPayload['discount_ends_at'];
        }

        $product->update($updatePayload);

        $this->syncVariantRows($product, $variantRows);
        $this->syncBundleItems($product, $bundleItems, $isBundle);

        $imageCount = count($allImages);
        $deletedCount = count($imagesToDelete);
        $message = "Product updated successfully";
        if ($imageCount > 0) {
            $message .= " with {$imageCount} image(s)";
        }
        if ($deletedCount > 0) {
            $message .= ". Deleted {$deletedCount} image(s)";
        }

        // Ensure session is saved before redirect
        $request->session()->save();
        
        \Log::info('Product updated successfully', [
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'session_id' => $request->session()->getId()
        ]);

        // Get auth_token if present to append to redirect
        $authToken = $request->input('auth_token') ?? $request->attributes->get('admin_auth_token');
        $redirectUrl = route('admin.products.index');
        if ($authToken) {
            $redirectUrl .= '?auth_token=' . $authToken;
        }

        // Clear product API cache so mobile app reflects the update immediately
        Cache::flush();

        return redirect($redirectUrl)->with('success', $message . '.');
    }

    private function sanitizeBundleItems(array $rawItems): array
    {
        return collect($rawItems)
            ->filter(function ($item) {
                return is_array($item)
                    && !empty($item['product_id'])
                    && !empty($item['quantity']);
            })
            ->map(function ($item) {
                return [
                    'product_id' => (int) $item['product_id'],
                    'quantity' => (int) $item['quantity'],
                ];
            })
            ->values()
            ->all();
    }

    private function normalizeProductDiscountInput(Request $request, float $resolvedPrice): array
    {
        $type = strtolower(trim((string) $request->input('discount_type', '')));
        if (!in_array($type, ['percent', 'fixed'], true)) {
            $type = null;
        }

        $rawValue = $request->input('discount_value');
        $value = is_numeric($rawValue) ? (float) $rawValue : 0;

        if (!$type || $value <= 0) {
            return [
                'discount_type' => null,
                'discount_value' => null,
                'discount_starts_at' => null,
                'discount_ends_at' => null,
            ];
        }

        if ($type === 'percent' && $value > 100) {
            throw ValidationException::withMessages([
                'discount_value' => 'Percentage discount cannot exceed 100%.',
            ]);
        }

        if ($type === 'fixed' && $resolvedPrice > 0 && $value > $resolvedPrice) {
            $value = $resolvedPrice;
        }

        return [
            'discount_type' => $type,
            'discount_value' => round($value, 2),
            'discount_starts_at' => $request->filled('discount_starts_at') ? $request->input('discount_starts_at') : null,
            'discount_ends_at' => $request->filled('discount_ends_at') ? $request->input('discount_ends_at') : null,
        ];
    }

    private function validateBundleItems(array $bundleItems, bool $isBundle, ?int $currentProductId = null): void
    {
        if (!$this->bundleFeatureEnabled()) {
            return;
        }

        if (!$isBundle) {
            return;
        }

        if (empty($bundleItems)) {
            throw ValidationException::withMessages([
                'bundle_items' => 'Add at least one product to create a bundle.',
            ]);
        }

        $productIds = collect($bundleItems)->pluck('product_id');
        if ($productIds->count() !== $productIds->unique()->count()) {
            throw ValidationException::withMessages([
                'bundle_items' => 'Bundle items must be unique. Remove duplicate products.',
            ]);
        }

        if ($currentProductId !== null && $productIds->contains($currentProductId)) {
            throw ValidationException::withMessages([
                'bundle_items' => 'A bundle cannot include itself as one of its items.',
            ]);
        }
    }

    private function syncBundleItems(Product $product, array $bundleItems, bool $isBundle): void
    {
        if (!$this->bundleFeatureEnabled()) {
            return;
        }

        ProductBundleItem::where('bundle_product_id', $product->id)->delete();

        if (!$isBundle || empty($bundleItems)) {
            return;
        }

        $now = now();
        $rows = collect($bundleItems)->map(function ($item) use ($product, $now) {
            return [
                'bundle_product_id' => $product->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->all();

        ProductBundleItem::insert($rows);
    }

    private function sanitizeVariantRows(array $rawRows): array
    {
        return collect($rawRows)
            ->filter(fn($row) => is_array($row))
            ->map(function ($row) {
                return [
                    'sku' => trim((string) ($row['sku'] ?? '')) ?: null,
                    'size' => trim((string) ($row['size'] ?? '')) ?: null,
                    'color' => trim((string) ($row['color'] ?? '')) ?: null,
                    'price' => isset($row['price']) && $row['price'] !== '' ? (float) $row['price'] : null,
                    'stock' => isset($row['stock']) && $row['stock'] !== '' ? (int) $row['stock'] : null,
                    'is_active' => filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true,
                ];
            })
            ->filter(function ($row) {
                return $row['price'] !== null
                    && $row['stock'] !== null
                    && ($row['size'] !== null || $row['color'] !== null);
            })
            ->values()
            ->all();
    }

    private function validateVariantRows(array $variantRows): void
    {
        if (empty($variantRows)) {
            return;
        }

        $combinationKeys = collect($variantRows)
            ->map(function ($row) {
                return strtolower((string) ($row['size'] ?? '')) . '|' . strtolower((string) ($row['color'] ?? ''));
            });

        if ($combinationKeys->count() !== $combinationKeys->unique()->count()) {
            throw ValidationException::withMessages([
                'variant_rows' => 'Variant combinations must be unique (size + color).',
            ]);
        }
    }

    private function syncVariantRows(Product $product, array $variantRows): void
    {
        if (!$this->ensureProductVariantsTableExists()) {
            return;
        }

        ProductVariant::where('product_id', $product->id)->delete();

        if (empty($variantRows)) {
            return;
        }

        $now = now();
        $rows = collect($variantRows)->map(function ($row) use ($product, $now) {
            return [
                'product_id' => $product->id,
                'sku' => $row['sku'],
                'size' => $row['size'],
                'color' => $row['color'],
                'price' => $row['price'],
                'stock' => $row['stock'],
                'is_active' => $row['is_active'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->all();

        ProductVariant::insert($rows);
    }

    /**
     * Ensure product variants table exists in deployments where migrations lag behind.
     */
    private function ensureProductVariantsTableExists(): bool
    {
        if (Schema::hasTable('product_variants')) {
            return true;
        }

        try {
            Schema::create('product_variants', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->onDelete('cascade');
                $table->string('sku')->nullable();
                $table->string('size')->nullable();
                $table->string('color')->nullable();
                $table->decimal('price', 10, 2);
                $table->integer('stock')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['product_id', 'is_active']);
                $table->index(['product_id', 'size']);
                $table->index(['product_id', 'color']);
            });
        } catch (\Throwable $e) {
            report($e);
        }

        return Schema::hasTable('product_variants');
    }

    /**
     * Ensure product discount columns exist in deployments where migrations lag behind.
     */
    private function ensureProductDiscountColumnsExist(): bool
    {
        if (Schema::hasColumns('products', ['discount_type', 'discount_value', 'discount_starts_at', 'discount_ends_at'])) {
            return true;
        }

        try {
            Schema::table('products', function (\Illuminate\Database\Schema\Blueprint $table) {
                if (!Schema::hasColumn('products', 'discount_type')) {
                    $table->string('discount_type', 20)->nullable()->after('price');
                }
                if (!Schema::hasColumn('products', 'discount_value')) {
                    $table->decimal('discount_value', 10, 2)->nullable()->after('discount_type');
                }
                if (!Schema::hasColumn('products', 'discount_starts_at')) {
                    $table->timestamp('discount_starts_at')->nullable()->after('discount_value');
                }
                if (!Schema::hasColumn('products', 'discount_ends_at')) {
                    $table->timestamp('discount_ends_at')->nullable()->after('discount_starts_at');
                }
            });
        } catch (\Throwable $e) {
            report($e);
        }

        return Schema::hasColumns('products', ['discount_type', 'discount_value', 'discount_starts_at', 'discount_ends_at']);
    }

    private function bundleFeatureEnabled(): bool
    {
        return Schema::hasTable('product_bundle_items');
    }

    /**
     * Add stock to the specified product.
     */
    public function stockIn(Request $request, Product $product)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);

        $qty = (int) $request->input('quantity');
        $note = $request->input('note');
        $authToken = $request->input('auth_token') ?? $request->query('auth_token');

        // Determine redirect back to edit page or product list
        $fromEdit = $request->boolean('from_edit');
        if ($fromEdit) {
            $base = route('admin.products.edit', $product->id);
        } else {
            $base = route('admin.products.index');
        }
        $redirectUrl = $authToken ? $base . '?auth_token=' . urlencode($authToken) : $base;

        if ($product->variants()->exists()) {
            return redirect($redirectUrl)->with('error', 'This product uses variants. Update stock per variant in the product edit form.');
        }

        if ($product->inventory) {
            $product->inventory->increment('quantity', $qty);
        } else {
            $product->increment('stock', $qty);
        }

        // Log the stock addition (guard against missing table during migration)
        if (\Schema::hasTable('stock_logs')) {
            \App\Models\StockLog::create([
                'product_id' => $product->id,
                'quantity'   => $qty,
                'note'       => $note,
                'created_by' => auth()->id(),
            ]);
        }

        Cache::flush();

        return redirect($redirectUrl)->with('success', "Added {$qty} unit(s) to \u201c{$product->name}\u201d. New stock: {$product->fresh()->available_stock}.");
    }

    /**
     * Remove stock from the specified product.
     */
    public function stockOut(Request $request, Product $product)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);

        $qty = (int) $request->input('quantity');
        $note = $request->input('note');
        $authToken = $request->input('auth_token') ?? $request->query('auth_token');

        // Determine redirect back to edit page or product list
        $fromEdit = $request->boolean('from_edit');
        if ($fromEdit) {
            $base = route('admin.products.edit', $product->id);
        } else {
            $base = route('admin.products.index');
        }
        $redirectUrl = $authToken ? $base . '?auth_token=' . urlencode($authToken) : $base;

        if ($product->variants()->exists()) {
            return redirect($redirectUrl)->with('error', 'This product uses variants. Update stock per variant in the product edit form.');
        }

        $currentStock = (int) $product->fresh()->available_stock;
        if ($qty > $currentStock) {
            return redirect($redirectUrl)->with('error', "Cannot stock out {$qty} unit(s). Available stock is only {$currentStock}.");
        }

        if ($product->inventory) {
            $product->inventory->decrement('quantity', $qty);
        } else {
            $product->decrement('stock', $qty);
        }

        // Log the stock deduction as negative quantity
        if (\Schema::hasTable('stock_logs')) {
            \App\Models\StockLog::create([
                'product_id' => $product->id,
                'quantity'   => -$qty,
                'note'       => $note,
                'created_by' => auth()->id(),
            ]);
        }

        Cache::flush();

        return redirect($redirectUrl)->with('success', "Removed {$qty} unit(s) from \u201c{$product->name}\u201d. New stock: {$product->fresh()->available_stock}.");
    }

    /**
     * Remove the specified product.
     */
    public function destroy(string $id)
    {
        // Extract auth_token for redirect preservation
        $authToken = request()->input('auth_token') ?? request()->query('auth_token');
        $redirectUrl = $authToken ? route('admin.products.index') . '?auth_token=' . urlencode($authToken) : route('admin.products.index');
        
        try {
            $product = Product::findOrFail($id);
            $productName = $product->name;

            // Delete associated image if exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            // Delete related records (optional - depends on your foreign key constraints)
            // If you have cascade delete set up in migrations, this is automatic
            // Otherwise, manually delete related records:
            // $product->orderItems()->delete();
            // $product->reviews()->delete();
            // $product->inventory()->delete();

            // Delete the product
            $product->delete();

            // Check if request expects JSON (AJAX request)
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Product '{$productName}' has been deleted successfully."
                ]);
            }

            // Clear product API cache so mobile app no longer shows the deleted product
            Cache::flush();

            return redirect($redirectUrl)
                           ->with('success', "Product '{$productName}' deleted successfully.");
                           
        } catch (\Exception $e) {
            \Log::error('Product deletion error: ' . $e->getMessage());
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete product. ' . $e->getMessage()
                ], 500);
            }

            return redirect($redirectUrl)
                           ->with('error', 'Failed to delete product.');
        }
    }
}
