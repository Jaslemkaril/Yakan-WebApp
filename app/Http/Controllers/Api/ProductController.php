<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\CustomOrder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class ProductController extends Controller
{
    private function isPlaceholderImage(?string $value): bool
    {
        if (!is_string($value) || trim($value) === '') {
            return false;
        }

        $normalized = strtolower(trim($value));
        return str_contains($normalized, '/images/no-image.svg')
            || str_contains($normalized, '\\images\\no-image.svg')
            || str_contains($normalized, '/images/yakanlogo.png')
            || str_contains($normalized, '\\images\\yakanlogo.png');
    }

    private function resolveVariantImageUrl(ProductVariant $variant): ?string
    {
        $resolved = trim((string) ($variant->image_src ?? ''));

        if ($resolved === '' || $this->isPlaceholderImage($resolved)) {
            $rawPath = trim((string) ($variant->image ?? ''));
            $fallback = $rawPath !== '' ? trim((string) storageAsset($rawPath)) : '';
            if ($fallback !== '' && !$this->isPlaceholderImage($fallback)) {
                $resolved = $fallback;
            } else {
                $resolved = '';
            }
        }

        return $resolved !== '' ? $resolved : null;
    }

    private function buildPriceMeta(Product $product, float $basePrice): array
    {
        $basePrice = max(0, $basePrice);
        $effectivePrice = (float) $product->getDiscountedPrice($basePrice);
        $discountAmount = (float) $product->getDiscountAmount($basePrice);

        return [
            'price' => $effectivePrice,
            'original_price' => round($basePrice, 2),
            'discount_amount' => $discountAmount,
            'has_product_discount' => $discountAmount > 0,
        ];
    }

    private function getActiveVariants(Product $product)
    {
        if ($product->relationLoaded('variants')) {
            return $product->variants->where('is_active', true)->values();
        }

        return $product->variants()
            ->where('is_active', true)
            ->get(['id', 'product_id', 'sku', 'size', 'color', 'image', 'price', 'stock', 'is_active']);
    }

    private function formatVariant(Product $product, ProductVariant $variant): array
    {
        $priceMeta = $this->buildPriceMeta($product, (float) $variant->price);
        $variantImageUrl = $this->resolveVariantImageUrl($variant);

        return [
            'id' => $variant->id,
            'sku' => $variant->sku,
            'size' => $variant->size,
            'color' => $variant->color,
            'image' => $variant->image,
            'image_url' => $variantImageUrl,
            'image_src' => $variantImageUrl,
            'price' => $priceMeta['price'],
            'original_price' => $priceMeta['original_price'],
            'discount_amount' => $priceMeta['discount_amount'],
            'has_product_discount' => $priceMeta['has_product_discount'],
            'stock' => (int) $variant->stock,
            'is_active' => (bool) $variant->is_active,
            'label' => collect([$variant->size, $variant->color])->filter()->implode(' / '),
        ];
    }

    private function getEffectiveStock(Product $product, ?ProductVariant $variant = null): int
    {
        if ($variant) {
            return (int) $variant->stock;
        }

        $activeVariants = $this->getActiveVariants($product);
        if ($activeVariants->isNotEmpty()) {
            return (int) $activeVariants->sum('stock');
        }

        // Check if product is a bundle - use dynamic stock calculation
        $isBundle = Schema::hasTable('product_bundle_items') 
            && $product->bundleItems()->exists();
        
        if ($isBundle) {
            return (int) $product->available_stock;
        }

        return (int) ($product->inventory?->quantity ?? $product->stock ?? 0);
    }

    private function getEffectivePrice(Product $product, ?ProductVariant $variant = null): float
    {
        if ($variant) {
            return $this->buildPriceMeta($product, (float) $variant->price)['price'];
        }

        $activeVariants = $this->getActiveVariants($product);
        if ($activeVariants->isNotEmpty()) {
            return $this->buildPriceMeta($product, (float) $activeVariants->min('price'))['price'];
        }

        return $this->buildPriceMeta($product, (float) $product->price)['price'];
    }

    private function decorateProduct(Product $product, bool $includeVariants = false): Product
    {
        $activeVariants = $this->getActiveVariants($product);
        $hasVariants = $activeVariants->isNotEmpty();
        $basePrice = $hasVariants
            ? (float) $activeVariants->min('price')
            : (float) $product->price;
        $priceMeta = $this->buildPriceMeta($product, $basePrice);

        $product->stock = $this->getEffectiveStock($product);
        $product->price = $priceMeta['price'];
        $product->setAttribute('original_price', $priceMeta['original_price']);
        $product->setAttribute('discount_amount', $priceMeta['discount_amount']);
        $product->setAttribute('has_product_discount', $priceMeta['has_product_discount']);
        $product->setAttribute('discount_type', $priceMeta['has_product_discount'] ? $product->discount_type : null);
        $product->setAttribute('discount_value', $priceMeta['has_product_discount'] ? (float) $product->discount_value : null);
        $productHasOwnImage = method_exists($product, 'hasImage') ? $product->hasImage() : !empty($product->image);
        $imageUrl = $product->image_url;
        if ($this->isPlaceholderImage($imageUrl)) {
            $imageUrl = null;
        }
        $imageSrc = $product->image_src;
        if ($this->isPlaceholderImage($imageSrc)) {
            $imageSrc = null;
        }
        $fallbackImagePath = null;

        if (!$productHasOwnImage || empty($imageUrl)) {
            // Variant products: fall back to first active variant's image
            if ($hasVariants) {
                $variantWithImage = $activeVariants->first(function ($v) {
                    return !empty($v->image);
                });
                if ($variantWithImage) {
                    $variantResolved = $this->resolveVariantImageUrl($variantWithImage);
                    if ($variantResolved) {
                        $imageUrl = $variantResolved;
                        $imageSrc = $variantResolved;
                        $fallbackImagePath = $variantWithImage->image;
                    }
                }
            }

            // Bundle products: fall back to first component product's image
            if (!$fallbackImagePath
                && Schema::hasTable('product_bundle_items')
                && $product->bundleItems()->exists()) {
                $firstBundleItem = $product->bundleItems()->with('componentProduct')->first();
                $componentProduct = $firstBundleItem?->componentProduct;
                if ($componentProduct) {
                    $componentResolved = $componentProduct->image_url;
                    if ($this->isPlaceholderImage($componentResolved)) {
                        $componentResolved = $componentProduct->image_src;
                    }
                    if ($this->isPlaceholderImage($componentResolved)) {
                        $componentResolved = null;
                    }
                    if (!empty($componentResolved)) {
                        $imageUrl = $componentResolved;
                        $imageSrc = $componentResolved;
                        $fallbackImagePath = $componentProduct->image;
                    }
                }
            }
        }

        if (empty($imageUrl) && !empty($imageSrc)) {
            $imageUrl = $imageSrc;
        }

        if (empty($imageSrc) && !empty($imageUrl)) {
            $imageSrc = $imageUrl;
        }

        if ($fallbackImagePath && empty($product->image)) {
            $product->setAttribute('image', $fallbackImagePath);
        }
        $product->setAttribute('image_url', $imageUrl);
        $product->setAttribute('image_src', $imageSrc);
        $product->setAttribute('has_variants', $hasVariants);
        $product->setAttribute('variant_count', $activeVariants->count());

        if ($hasVariants) {
            $defaultVariant = $activeVariants->first();
            $product->setAttribute('default_variant', $defaultVariant ? $this->formatVariant($product, $defaultVariant) : null);

            if ($includeVariants) {
                $product->setAttribute('variants', $activeVariants->map(fn(ProductVariant $variant) => $this->formatVariant($product, $variant))->values());
            }
        } else {
            $product->setAttribute('default_variant', null);
            if ($includeVariants) {
                $product->setAttribute('variants', []);
            }
        }

        return $product;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $cacheKey = 'products:' . md5(json_encode($request->all()));
            
            $products = Cache::remember($cacheKey, env('PRODUCT_CACHE_TTL', 300), function () use ($request) {
                $selectColumns = ['id', 'name', 'description', 'price', 'stock', 'category_id', 'image', 'all_images', 'status', 'sku', 'created_at'];
                if (Schema::hasColumns('products', ['discount_type', 'discount_value', 'discount_starts_at', 'discount_ends_at'])) {
                    array_splice($selectColumns, 4, 0, ['discount_type', 'discount_value', 'discount_starts_at', 'discount_ends_at']);
                }

                $query = Product::select($selectColumns)->with([
                    'category:id,name,slug',
                    'inventory:product_id,quantity',
                    'variants:id,product_id,sku,size,color,image,price,stock,is_active',
                ])->active(); // show all active products; out-of-stock handled in app
                
                if ($request->has('category')) {
                    $query->where('category_id', $request->category);
                }
                
                if ($request->has('search')) {
                    $search = $request->search;
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%')
                          ->orWhere('description', 'like', '%' . $search . '%');
                    });
                }
                
                if ($request->has('featured') && $request->featured == 'true') {
                    $query->where('featured', true);
                }
                
                if ($request->has('sort_by')) {
                    $sortBy = $request->sort_by;
                    $sortOrder = $request->get('sort_order', 'asc');
                    
                    if (in_array($sortBy, ['name', 'price', 'created_at'])) {
                        $query->orderBy($sortBy, $sortOrder);
                    }
                } else {
                    // Default sorting: newest first
                    $query->orderBy('created_at', 'desc');
                }
                
                $perPage = $request->get('per_page', 12);
                
                if ($request->has('limit')) {
                    $products = $query->limit($request->limit)->get();
                } else {
                    $products = $query->limit($perPage)->get();
                }

                $products->each(function ($product) {
                    $this->decorateProduct($product, false);
                });

                return $products;
            });

            return response()->json([
                'success' => true,
                'data' => $products,
                'count' => count($products)
            ]);
        } catch (\Exception $e) {
            \Log::error('Products API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch products',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function featured(Request $request): JsonResponse
    {
        try {
            $cacheKey = 'products:featured';
            
            $products = Cache::remember($cacheKey, env('PRODUCT_CACHE_TTL', 7200), function () use ($request) {
                $selectColumns = ['id', 'name', 'description', 'price', 'stock', 'category_id', 'image', 'all_images', 'status'];
                if (Schema::hasColumns('products', ['discount_type', 'discount_value', 'discount_starts_at', 'discount_ends_at'])) {
                    array_splice($selectColumns, 4, 0, ['discount_type', 'discount_value', 'discount_starts_at', 'discount_ends_at']);
                }

                $products = Product::select($selectColumns)
                ->with([
                    'category:id,name,slug',
                    'inventory:product_id,quantity',
                    'variants:id,product_id,sku,size,color,image,price,stock,is_active',
                ])
                ->active()
                ->where('featured', true)
                ->limit($request->get('limit', 6))
                ->get();

                $products = $products->filter(function ($product) {
                    return $this->getEffectiveStock($product) > 0;
                })->values();

                $products->each(function ($product) {
                    $this->decorateProduct($product, false);
                });

                return $products;
            });

            return response()->json([
                'success' => true,
                'data' => $products,
                'count' => count($products)
            ]);
        } catch (\Exception $e) {
            \Log::error('Featured Products API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch featured products',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        $productId = (int) $id;
        $cacheKey = "product:v2:{$productId}";
        
        $product = Cache::remember($cacheKey, env('PRODUCT_CACHE_TTL', 7200), function () use ($productId) {
            $productModel = Product::query()->findOrFail($productId);

            $productModel->load([
                'category',
                'orderItems',
                'inventory',
                'variants:id,product_id,sku,size,color,image,price,stock,is_active',
            ]);

            return $this->decorateProduct($productModel, true);
        });

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    public function byCategory($category): JsonResponse
    {
        $cacheKey = "products:category:{$category}";
        
        $products = Cache::remember($cacheKey, env('CATEGORY_CACHE_TTL', 86400), function () use ($category) {
            return Product::with([
                'category',
                'inventory:product_id,quantity',
                'variants:id,product_id,sku,size,color,image,price,stock,is_active',
            ])
                ->whereHas('category', function($query) use ($category) {
                    $query->where('slug', $category);
                })
                ->active()
                ->paginate(12);
        });

        $products->getCollection()->transform(function ($product) {
            return $this->decorateProduct($product, false);
        });

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }
}
