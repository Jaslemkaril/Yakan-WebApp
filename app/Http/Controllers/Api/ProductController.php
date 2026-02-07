<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\CustomOrder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $cacheKey = 'products:' . md5(json_encode($request->all()));
            
            $products = Cache::remember($cacheKey, env('PRODUCT_CACHE_TTL', 3600), function () use ($request) {
                $query = Product::select([
                    'id', 'name', 'description', 'price', 'stock', 'category_id', 'image', 'status', 'sku', 'created_at'
                ])->with('category:id,name,slug')->active();
                
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
                    return $query->limit($request->limit)->get();
                }
                
                return $query->limit($perPage)->get();
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
                return Product::select([
                    'id', 'name', 'description', 'price', 'stock', 'category_id', 'image', 'status'
                ])
                ->with('category:id,name,slug')
                ->active()
                ->where('featured', true)
                ->limit($request->get('limit', 6))
                ->get();
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

    public function show(Product $product): JsonResponse
    {
        $cacheKey = "product:{$product->id}";
        
        $product = Cache::remember($cacheKey, env('PRODUCT_CACHE_TTL', 7200), function () use ($product) {
            return $product->load(['category', 'orderItems']);
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
            return Product::with('category')
                ->whereHas('category', function($query) use ($category) {
                    $query->where('slug', $category);
                })
                ->active()
                ->paginate(12);
        });

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }
}
