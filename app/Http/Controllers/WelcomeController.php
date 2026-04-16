<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class WelcomeController extends Controller
{
    public function index()
    {
        $hasProductsTable = Schema::hasTable('products');
        $hasCategoriesTable = Schema::hasTable('categories');
        $hasReviewsTable = Schema::hasTable('reviews');
        $hasUsersTable = Schema::hasTable('users');
        $hasWishlistsTable = Schema::hasTable('wishlists') && Schema::hasTable('wishlist_items');

        // Check if database has data (only seed once in production)
        // This helps with Railway deployments where database might be ephemeral
        $hasActiveProducts = $hasProductsTable
            ? Product::where('status', 'active')->exists()
            : false;

        if ($hasProductsTable && !$hasActiveProducts && config('app.env') === 'production') {
            // Only auto-seed in production on Railway (ephemeral filesystem)
            try {
                Artisan::call('db:seed', ['--force' => true]);
                // Refresh query after seeding
                $hasActiveProducts = true;
            } catch (\Exception $e) {
                // Log error but continue - don't break the homepage
                Log::error('Failed to seed database: ' . $e->getMessage());
            }
        }

        // Latest 8 active products
        $latestProducts = $hasProductsTable
            ? Product::where('status', 'active')
                ->latest()
                ->take(8)
                ->get()
            : collect();

        // Featured products (example: you could use a 'featured' flag)
        $featuredProducts = $hasProductsTable
            ? Product::with('category')
                ->where('status', 'active')
                ->latest()
                ->take(8)
                ->get()
            : collect();

        // Fetch all categories with their top 3 active products
        $categories = ($hasCategoriesTable && $hasProductsTable)
            ? Category::with(['products' => function ($query) {
                $query->where('status', 'active')
                    ->latest()
                    ->take(3);
            }])->get()
            : collect();

        // Total number of active products
        $totalProducts = $hasProductsTable
            ? Product::where('status', 'active')->count()
            : 0;

        // Preload wishlist product ids so homepage hearts reflect saved state.
        $wishlistProductIds = [];
        if (Auth::check() && $hasWishlistsTable && $hasProductsTable) {
            $wishlist = Auth::user()->wishlists()->default()->first();
            if ($wishlist) {
                $wishlistProductIds = $wishlist->items()
                    ->where('item_type', Product::class)
                    ->pluck('item_id')
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->toArray();
            }
        }

        // Real approved reviews for testimonials section
        $testimonials = ($hasReviewsTable && $hasUsersTable)
            ? Review::with(['user', 'product', 'customOrder.product'])
                ->where('is_approved', true)
                ->whereNotNull('comment')
                ->whereHas('user')
                ->where(function ($query) {
                    $query->whereHas('product')
                        ->orWhereHas('customOrder');
                })
                ->orderByDesc('rating')
                ->orderByDesc('created_at')
                ->take(6)
                ->get()
            : collect();

        // Pass all variables to the view
        return view('welcome', compact(
            'latestProducts',
            'featuredProducts',
            'categories',
            'totalProducts',
            'wishlistProductIds',
            'testimonials'
        ));
    }

    public function contact()
    {
        return view('contact');
    }

    public function submitContact(Request $request)
    {
        // Handle contact form submission
        return redirect()->back()->with('success', 'Message sent successfully!');
    }
}
