<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of all active products for the shop.
     *
     * @return \Illuminate\View\View
     */
    public function shopIndex(Request $request)
    {
        try {
            // Get all categories with product counts
            $categories = Category::withCount(['products' => function($query) {
                $query->where('status', 'active');
            }])->orderBy('name')->get();
            
            // Start with base query for active products
            $query = Product::where('status', 'active');
            
            // Filter by category if specified
            $selectedCategory = null;
            if ($request->has('category') && $request->category !== 'all') {
                $selectedCategory = Category::where('slug', $request->category)->first();
                if ($selectedCategory) {
                    $query->where('category_id', $selectedCategory->id);
                }
            }
            
            // Fetch products with pagination and category relationship
            $products = $query->with(['category', 'inventory'])->paginate(12);

            // Return the products view with products, categories, and selected category
            return view('products.index', compact('products', 'categories', 'selectedCategory'));
        } catch (\Exception $e) {
            // Log the error and return a simple response
            \Log::error('ProductController::shopIndex error: ' . $e->getMessage());
            
            // Fallback to simple products query
            $products = Product::paginate(12);
            return view('products.index', compact('products'));
        }
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
            // Eager load inventory so show.blade.php can use $product->inventory->quantity
            $product->load('inventory');

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

            // Return the product details view
            return view('products.show', compact('product', 'relatedProducts'));
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
        // Get all categories with product counts
        $categories = Category::withCount(['products' => function($query) {
            $query->where('status', 'active');
        }])->orderBy('name')->get();
        
        // Find the category by slug
        $selectedCategory = null;
        if ($category === 'all') {
            $products = Product::where('status', 'active')->with(['category', 'inventory'])->paginate(12);
        } else {
            $selectedCategory = Category::where('slug', $category)->first();
            if ($selectedCategory) {
                $products = Product::where('category_id', $selectedCategory->id)
                                    ->where('status', 'active')
                                    ->with(['category', 'inventory'])
                                    ->paginate(12);
            } else {
                // Fallback to all if category not found
                $products = Product::where('status', 'active')->with(['category', 'inventory'])->paginate(12);
            }
        }

        return view('products.index', compact('products', 'categories', 'selectedCategory'));
    }

    /**
     * Search products
     */
    public function search(Request $request)
    {
        try {
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

            $products = $productsQuery->with(['category', 'inventory'])->paginate(12)->appends($request->all());

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
