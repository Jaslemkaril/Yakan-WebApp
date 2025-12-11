<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Get all products with filtering
     */
    public function index(Request $request)
    {
        try {
            $query = Product::query();

            // Filter by category
            if ($request->has('category') && $request->category) {
                $query->where('category', $request->category);
            }

            // Filter by featured
            if ($request->has('featured') && $request->featured) {
                $query->where('featured', true);
            }

            // Search
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where('name', 'like', "%$search%")
                      ->orWhere('description', 'like', "%$search%");
            }

            // Sorting
            $sort = $request->get('sort', 'name');
            if ($sort === '-created_at') {
                $query->orderByDesc('created_at');
            } elseif ($sort === 'price') {
                $query->orderBy('price');
            } elseif ($sort === '-price') {
                $query->orderByDesc('price');
            } else {
                $query->orderBy('name');
            }

            // Pagination
            $perPage = $request->get('per_page', 20);
            $products = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Products retrieved successfully',
                'data' => [
                    'products' => $products->items(),
                    'total' => $products->total(),
                    'per_page' => $products->perPage(),
                    'current_page' => $products->currentPage(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve products: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single product
     */
    public function show($id)
    {
        try {
            $product = Product::findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Product retrieved successfully',
                'data' => $product,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found: ' . $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Search products
     */
    public function search(Request $request)
    {
        try {
            $query = $request->get('q', '');

            if (empty($query)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Search query is required',
                ], 400);
            }

            $products = Product::where('name', 'like', "%$query%")
                               ->orWhere('description', 'like', "%$query%")
                               ->limit(20)
                               ->get();

            return response()->json([
                'success' => true,
                'message' => 'Search results retrieved successfully',
                'data' => [
                    'products' => $products,
                    'count' => $products->count(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
