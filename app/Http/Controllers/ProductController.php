<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;

class ProductController extends Controller
{
    private function productRelationsForListing(): array
    {
        $relations = ['category', 'inventory'];

        if (Schema::hasTable('product_bundle_items')) {
            $relations[] = 'bundleItems.componentProduct';
        }

        return $relations;
    }

    private function productWithCountColumns(): array
    {
        $counts = ['reviews'];

        if (Schema::hasTable('product_bundle_items')) {
            $counts[] = 'bundleItems';
        }

        return $counts;
    }

    private function productDetailRelations(): array
    {
        $relations = [
            'inventory',
            'variants' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('size')
                    ->orderBy('color')
                    ->orderBy('id');
            },
        ];

        if (Schema::hasTable('product_bundle_items')) {
            $relations[] = 'bundleItems.componentProduct';
        }

        return $relations;
    }

    /**
     * Display a listing of all active products for the shop.
     *
     * @return \Illuminate\View\View
     */
    public function shopIndex(Request $request)
    {
        if (!Schema::hasTable('products') || !Schema::hasTable('categories')) {
            $products = new LengthAwarePaginator(
                collect(),
                0,
                12,
                max(1, (int) $request->input('page', 1)),
                ['path' => $request->url(), 'query' => $request->query()]
            );

            return view('products.index', [
                'products' => $products,
                'categories' => collect(),
                'selectedCategory' => null,
                'wishlistProductIds' => [],
                'priceStats' => (object) ['min_p' => null, 'max_p' => null],
            ]);
        }

        // Get all categories with product counts
        $categories = Category::withCount(['products' => function($query) {
            $query->where('status', 'active');
        }])->orderBy('name')->get();

        // Start with base query for active products
        $query = Product::where('status', 'active');

        // Filter by category if specified
        $selectedCategory = null;
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
            $selectedCategory = Category::find($request->category);
        }

        // Filter by price range — cast to float so comparisons are always numeric
        $minPrice = $request->filled('min_price') ? (float) $request->min_price : null;
        $maxPrice = $request->filled('max_price') ? (float) $request->max_price : null;
        if ($minPrice !== null) {
            $query->where('price', '>=', $minPrice);
        }
        if ($maxPrice !== null) {
            $query->where('price', '<=', $maxPrice);
        }

        // Sorting
        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // Fetch products with pagination and category relationship
        $products = $query
            ->with($this->productRelationsForListing())
            ->withCount($this->productWithCountColumns())
            ->paginate(12)
            ->appends($request->all());

        // Get wishlist items if user is authenticated
        $wishlistProductIds = [];
        try {
            if (auth()->check()) {
                $wishlist = auth()->user()->wishlists()->default()->first();
                if ($wishlist) {
                    $wishlistProductIds = $wishlist->items()
                        ->where('item_type', 'App\Models\Product')
                        ->pluck('item_id')
                        ->toArray();
                }
            }
        } catch (\Exception $e) {
            \Log::warning('ProductController: wishlist load failed: ' . $e->getMessage());
        }

        // Pass actual product price bounds so the view can show accurate range hints
        $priceStats = Product::where('status', 'active')->selectRaw('MIN(price) as min_p, MAX(price) as max_p')->first();

        // Return the products view with products, categories, and selected category
        return view('products.index', compact('products', 'categories', 'selectedCategory', 'wishlistProductIds', 'priceStats'));
    }

    /**
     * Display the details of a single product.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\View\View
     */
    public function show(Product $product)
    {
        try {
            // Eager load inventory + active variants for storefront rendering.
            $product->load($this->productDetailRelations());

            $activeVariants = $product->variants->values();
            $hasVariants = $activeVariants->isNotEmpty();
            $defaultVariant = $hasVariants
                ? ($activeVariants->firstWhere('stock', '>', 0) ?: $activeVariants->first())
                : null;

            $initialOriginalPrice = $defaultVariant
                ? (float) $defaultVariant->price
                : (float) $product->price;
            $initialDisplayPrice = (float) $product->getDiscountedPrice($initialOriginalPrice);
            $initialAvailableStock = $defaultVariant
                ? (int) $defaultVariant->stock
                : (int) ($product->inventory?->quantity ?? $product->stock ?? 0);

            // Track recent view
            if (auth()->check()) {
                \App\Models\RecentView::track($product, auth()->id());
            }

            // Get related products by category
            $relatedProducts = [];
            if ($product->category_id) {
                $relatedProducts = \App\Models\Product::where('category_id', $product->category_id)
                    ->where('id', '!=', $product->id)
                    ->where('status', 'active')
                    ->inRandomOrder()
                    ->limit(4)
                    ->get();
            }

            // Reviews — find if logged-in user can leave a review for this product
            $userOrderItem = null;
            $userReview    = null;
            if (auth()->check()) {
                $userId = auth()->id();
                // IDs of order items this user already reviewed
                $reviewedItemIds = \App\Models\Review::where('user_id', $userId)
                    ->whereNotNull('order_item_id')
                    ->pluck('order_item_id');
                // First unreviewed delivered/completed order item for this product
                $userOrderItem = \App\Models\OrderItem::where('product_id', $product->id)
                    ->whereNotIn('id', $reviewedItemIds)
                    ->whereHas('order', function ($q) use ($userId) {
                        $q->where('user_id', $userId)
                          ->whereIn('status', ['delivered', 'completed']);
                    })
                    ->latest('id')
                    ->first();
                // Existing review from this user for this product
                $userReview = \App\Models\Review::where('product_id', $product->id)
                    ->where('user_id', $userId)
                    ->latest()
                    ->first();
            }

            // Return the product details view
            return view('products.show', compact(
                'product',
                'relatedProducts',
                'userOrderItem',
                'userReview',
                'activeVariants',
                'hasVariants',
                'defaultVariant',
                'initialOriginalPrice',
                'initialDisplayPrice',
                'initialAvailableStock'
            ));
        } catch (\Exception $e) {
            \Log::error('ProductController::show error: ' . $e->getMessage());
            
            // Return to products page with error message
            return redirect()->route('products.index')->with('error', 'Product not found');
        }
    }

    /**
     * Display products by category slug or all.
     */
    public function byCategory($category)
    {
        if (!Schema::hasTable('products') || !Schema::hasTable('categories')) {
            $products = new LengthAwarePaginator(
                collect(),
                0,
                12,
                1,
                ['path' => route('products.index')]
            );

            return view('products.index', [
                'products' => $products,
                'categories' => collect(),
                'selectedCategory' => null,
                'wishlistProductIds' => [],
            ]);
        }

        // Get all categories with product counts
        $categories = Category::withCount(['products' => function($query) {
            $query->where('status', 'active');
        }])->orderBy('name')->get();
        
        // Find the category by slug
        $selectedCategory = null;
        if ($category === 'all') {
            $products = Product::where('status', 'active')
                ->with($this->productRelationsForListing())
                ->withCount($this->productWithCountColumns())
                ->paginate(12);
        } else {
            $selectedCategory = Category::where('slug', $category)->first();
            if ($selectedCategory) {
                $products = Product::where('category_id', $selectedCategory->id)
                                    ->where('status', 'active')
                                    ->with($this->productRelationsForListing())
                                    ->withCount($this->productWithCountColumns())
                                    ->paginate(12);
            } else {
                // Fallback to all if category not found
                $products = Product::where('status', 'active')
                    ->with($this->productRelationsForListing())
                    ->withCount($this->productWithCountColumns())
                    ->paginate(12);
            }
        }

        // Get wishlist items if user is authenticated
        $wishlistProductIds = [];
        if (auth()->check()) {
            $wishlist = auth()->user()->wishlists()->default()->first();
            if ($wishlist) {
                $wishlistProductIds = $wishlist->items()
                    ->where('item_type', 'App\Models\Product')
                    ->pluck('item_id')
                    ->toArray();
            }
        }

        return view('products.index', compact('products', 'categories', 'selectedCategory', 'wishlistProductIds'));
    }

    /**
     * Search products
     */
    public function search(Request $request)
    {
        try {
            if (!Schema::hasTable('products') || !Schema::hasTable('categories')) {
                return view('products.search', [
                    'products' => new LengthAwarePaginator(
                        collect(),
                        0,
                        12,
                        max(1, (int) $request->input('page', 1)),
                        ['path' => $request->url(), 'query' => $request->query()]
                    ),
                    'categories' => collect(),
                    'selectedCategory' => null,
                    'query' => $request->input('q', ''),
                ]);
            }

            $query = $request->input('q', '');
            $category = $request->input('category');
            $minPrice = $request->input('min_price');
            $maxPrice = $request->input('max_price');
            $sort = $request->input('sort', 'relevance');

            \Log::info('Product search', [
                'query' => $query,
                'category' => $category,
                'sort' => $sort
            ]);

            // Get all categories for filter
            $categories = Category::withCount(['products' => function($q) {
                $q->where('status', 'active');
            }])->orderBy('name')->get();

            // Build search query
            $productsQuery = Product::where('status', 'active');

            // Search by name or description
            if (!empty($query)) {
                $productsQuery->where(function($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                      ->orWhere('description', 'LIKE', "%{$query}%")
                      ->orWhere('sku', 'LIKE', "%{$query}%");
                });
            }

            // Filter by category
            if (!empty($category) && $category !== 'all') {
                $productsQuery->where('category_id', $category);
            }

            // Filter by price range
            if (!empty($minPrice)) {
                $productsQuery->where('price', '>=', $minPrice);
            }
            if (!empty($maxPrice)) {
                $productsQuery->where('price', '<=', $maxPrice);
            }

            // Sorting
            switch ($sort) {
                case 'price_low':
                    $productsQuery->orderBy('price', 'asc');
                    break;
                case 'price_high':
                    $productsQuery->orderBy('price', 'desc');
                    break;
                case 'name_asc':
                    $productsQuery->orderBy('name', 'asc');
                    break;
                case 'name_desc':
                    $productsQuery->orderBy('name', 'desc');
                    break;
                case 'newest':
                    $productsQuery->orderBy('created_at', 'desc');
                    break;
                default: // relevance
                    if (!empty($query)) {
                        // Prioritize exact matches in name
                        $productsQuery->orderByRaw("CASE WHEN name LIKE ? THEN 1 ELSE 2 END", ["%{$query}%"]);
                    }
                    $productsQuery->orderBy('created_at', 'desc');
            }

            $products = $productsQuery
                ->with($this->productRelationsForListing())
                ->withCount($this->productWithCountColumns())
                ->paginate(12)
                ->appends($request->all());

            $selectedCategory = null;
            if (!empty($category) && $category !== 'all') {
                $selectedCategory = Category::find($category);
            }

            return view('products.search', compact('products', 'categories', 'selectedCategory', 'query'));
        } catch (\Exception $e) {
            \Log::error('Product search error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('products.index')
                ->with('error', 'Search failed. Please try again.');
        }
    }
}
