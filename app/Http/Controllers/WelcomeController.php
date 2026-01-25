<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class WelcomeController extends Controller
{
    public function index()
    {
        // Check if database has data (only seed once in production)
        // This helps with Railway deployments where database might be ephemeral
        $hasActiveProducts = Product::where('status', 'active')->exists();

        if (!$hasActiveProducts && config('app.env') === 'production') {
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
        $latestProducts = Product::where('status', 'active')
            ->latest()
            ->take(8)
            ->get();

        // Featured products (example: you could use a 'featured' flag)
        $featuredProducts = Product::with('category')
            ->where('status', 'active')
            ->latest()
            ->take(8)
            ->get();

        // Fetch all categories with their top 3 active products
        $categories = Category::with(['products' => function ($query) {
            $query->where('status', 'active')
                ->latest()
                ->take(3);
        }])->get();

        // Total number of active products
        $totalProducts = Product::where('status', 'active')->count();

        // Pass all variables to the view
        return view('welcome', compact(
            'latestProducts',
            'featuredProducts',
            'categories',
            'totalProducts'
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
