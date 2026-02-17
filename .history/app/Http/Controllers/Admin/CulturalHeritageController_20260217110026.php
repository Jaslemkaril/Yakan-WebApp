<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CulturalHeritage;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CulturalHeritageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = CulturalHeritage::query();

        // Search
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_published', $request->status === 'published');
        }

        $heritages = $query->ordered()->paginate(12);

        return view('admin.cultural-heritage.index', compact('heritages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.cultural-heritage.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string|max:500',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'category' => 'required|in:history,tradition,culture,art,crafts,language',
            'order' => 'nullable|integer|min:0',
            'is_published' => 'boolean',
            'author' => 'nullable|string|max:255',
            'published_date' => 'nullable|date',
        ]);

        // Generate slug
        $validated['slug'] = Str::slug($validated['title']);

        // Handle image upload
        if ($request->hasFile('image')) {
            try {
                $image = $request->file('image');
                $validated['image'] = $image->store('cultural-heritage', 'uploads');
                \Log::info('Cultural heritage image uploaded', [
                    'path' => $validated['image'],
                    'original_name' => $image->getClientOriginalName()
                ]);
            } catch (\Exception $e) {
                \Log::error('Cultural heritage image upload failed', [
                    'error' => $e->getMessage()
                ]);
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Failed to upload image: ' . $e->getMessage());
            }
        }

        // Set defaults
        $validated['is_published'] = $request->has('is_published');
        $validated['order'] = $validated['order'] ?? 0;

        CulturalHeritage::create($validated);

        return redirect()->route('admin.cultural-heritage.index')
                        ->with('success', 'Cultural heritage content created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $heritage = CulturalHeritage::findOrFail($id);
        return view('admin.cultural-heritage.show', compact('heritage'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $heritage = CulturalHeritage::findOrFail($id);
        return view('admin.cultural-heritage.edit', compact('heritage'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $heritage = CulturalHeritage::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string|max:500',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'category' => 'required|in:history,tradition,culture,art,crafts,language',
            'order' => 'nullable|integer|min:0',
            'is_published' => 'boolean',
            'author' => 'nullable|string|max:255',
            'published_date' => 'nullable|date',
        ]);

        // Update slug if title changed
        if ($heritage->title !== $validated['title']) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            try {
                // Delete old image
                if ($heritage->image) {
                    Storage::disk('uploads')->delete($heritage->image);
                }
                $image = $request->file('image');
                $validated['image'] = $image->store('cultural-heritage', 'uploads');
                \Log::info('Cultural heritage image updated', [
                    'heritage_id' => $heritage->id,
                    'path' => $validated['image'],
                    'original_name' => $image->getClientOriginalName()
                ]);
            } catch (\Exception $e) {
                \Log::error('Cultural heritage image update failed', [
                    'heritage_id' => $heritage->id,
                    'error' => $e->getMessage()
                ]);
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Failed to upload image: ' . $e->getMessage());
            }
        }

        $validated['is_published'] = $request->has('is_published');
        $validated['order'] = $validated['order'] ?? $heritage->order;

        $heritage->update($validated);

        return redirect()->route('admin.cultural-heritage.index')
                        ->with('success', 'Cultural heritage content updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $heritage = CulturalHeritage::findOrFail($id);
            $title = $heritage->title;

            // Delete image if exists
            if ($heritage->image) {
                Storage::disk('public')->delete($heritage->image);
            }

            $heritage->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "'{$title}' has been deleted successfully."
                ]);
            }

            return redirect()->route('admin.cultural-heritage.index')
                           ->with('success', "'{$title}' deleted successfully.");
                           
        } catch (\Exception $e) {
            \Log::error('Cultural heritage deletion error: ' . $e->getMessage());
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete content.'
                ], 500);
            }

            return redirect()->route('admin.cultural-heritage.index')
                           ->with('error', 'Failed to delete content.');
        }
    }

    /**
     * Toggle published status
     */
    public function toggleStatus(string $id)
    {
        $heritage = CulturalHeritage::findOrFail($id);
        $heritage->is_published = !$heritage->is_published;
        $heritage->save();

        return response()->json([
            'success' => true,
            'is_published' => $heritage->is_published,
            'message' => 'Status updated successfully.'
        ]);
    }
}
