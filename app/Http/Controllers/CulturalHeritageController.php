<?php

namespace App\Http\Controllers;

use App\Models\CulturalHeritage;
use Illuminate\Http\Request;

class CulturalHeritageController extends Controller
{
    /**
     * Display the cultural heritage page
     */
    public function index(Request $request)
    {
        $query = CulturalHeritage::where('is_published', true)->orderBy('order', 'asc');

        // Filter by category if specified
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $heritages = $query->get();
        
        // Get featured/main content - prioritize "Yakan Weaving"
        $featured = CulturalHeritage::where('is_published', true)
            ->where('slug', 'yakan-weaving-living-heritage')
            ->first();
        
        // If featured doesn't exist, use first ordered item
        if (!$featured) {
            $featured = $heritages->first();
        }

        return view('cultural-heritage.index', compact('heritages', 'featured'));
    }

    /**
     * Display a specific heritage content
     */
    public function show($slug)
    {
        $heritage = CulturalHeritage::where('slug', $slug)
                                   ->where('is_published', true)
                                   ->firstOrFail();

        // Get related content (same category, different item)
        $related = CulturalHeritage::where('is_published', true)
                                  ->where('category', $heritage->category)
                                  ->where('id', '!=', $heritage->id)
                                  ->orderBy('order', 'asc')
                                  ->limit(3)
                                  ->get();

        return view('cultural-heritage.show', compact('heritage', 'related'));
    }
}
