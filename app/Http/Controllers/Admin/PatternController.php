<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\YakanPattern;
use App\Models\PatternTag;
use App\Models\PatternMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class PatternController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request)
    {
        $query = YakanPattern::with('media', 'tags');

        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }
        if ($request->filled('difficulty')) {
            $query->byDifficulty($request->difficulty);
        }
        if ($request->filled('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('slug', $request->tag);
            });
        }

        $patterns = $query->latest()->paginate(12);
        $tags = PatternTag::all();

        return view('admin.patterns.index', compact('patterns', 'tags'));
    }

    public function create()
    {
        $tags = PatternTag::all();
        return view('admin.patterns.create', compact('tags'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'difficulty_level' => 'required|in:simple,medium,complex',
            'production_days' => 'required|integer|min:1|max:180',
            'pattern_price' => 'required|numeric|min:0',
            'price_per_meter' => 'required|numeric|min:0',
            'base_color' => 'nullable|string|max:50',
            'color_variations' => 'nullable|array',
            'is_active' => 'nullable|boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:pattern_tags,id',
            'svg_file' => 'nullable|file|mimes:svg|max:2048',
            'media' => 'nullable|array',
            'media.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
            'media_alt' => 'nullable|array',
            'media_alt.*' => 'nullable|string|max:255',
        ]);

        // Set is_active default value if not present
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        // Handle SVG file upload
        $svgFileName = null;
        if ($request->hasFile('svg_file')) {
            $svgFile = $request->file('svg_file');
            $svgFileName = Str::slug($request->name) . '-' . time() . '.svg';
            
            // Create directory if it doesn't exist
            $svgDirectory = public_path('uploads/patterns/svg');
            if (!file_exists($svgDirectory)) {
                mkdir($svgDirectory, 0755, true);
            }
            
            $svgFile->move($svgDirectory, $svgFileName);
            $validated['svg_path'] = $svgFileName;
        }

        $pattern = YakanPattern::create($validated);

        if (!empty($validated['tags'])) {
            $pattern->tags()->sync($validated['tags']);
        }

        // Automatic media record creation for SVG file (for user-side visibility)
        if ($svgFileName) {
            $pattern->media()->create([
                'type' => 'svg',
                'path' => 'patterns/svg/' . $svgFileName,
                'alt_text' => $pattern->name . ' pattern',
                'sort_order' => 0,
            ]);
        }

        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $index => $file) {
                // Store file in public/uploads/patterns directory
                $filename = time() . '_' . $index . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/patterns'), $filename);
                
                $pattern->media()->create([
                    'type' => 'image',
                    'path' => 'patterns/' . $filename,
                    'alt_text' => $request->input("media_alt.{$index}") ?? $pattern->name . ' pattern',
                    'sort_order' => $index + 1,
                ]);
            }
        }

        return redirect()->route('admin.patterns.index')->with('success', 'Pattern created successfully.');
    }

    public function show(YakanPattern $pattern)
    {
        $pattern->load('media', 'tags');
        return view('admin.patterns.show', compact('pattern'));
    }

    public function edit(YakanPattern $pattern)
    {
        $pattern->load('media', 'tags');
        $tags = PatternTag::all();
        return view('admin.patterns.edit', compact('pattern', 'tags'));
    }

    public function update(Request $request, YakanPattern $pattern)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'difficulty_level' => 'required|in:simple,medium,complex',
            'production_days' => 'required|integer|min:1|max:180',
            'pattern_price' => 'required|numeric|min:0',
            'price_per_meter' => 'required|numeric|min:0',
            'base_color' => 'nullable|string|max:50',
            'color_variations' => 'nullable|array',
            'is_active' => 'boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:pattern_tags,id',
            'svg_file' => 'nullable|file|mimes:svg|max:2048',
            'media' => 'nullable|array',
            'media.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
            'media_alt' => 'nullable|array',
            'media_alt.*' => 'nullable|string|max:255',
        ]);

        // Handle SVG file upload
        $newSvgFileName = null;
        if ($request->hasFile('svg_file')) {
            // Delete old SVG if exists
            if ($pattern->svg_path) {
                $oldSvgPath = public_path('uploads/patterns/svg/' . $pattern->svg_path);
                if (file_exists($oldSvgPath)) {
                    unlink($oldSvgPath);
                }
                // Delete old SVG media record
                $pattern->media()->where('type', 'svg')->delete();
            }
            
            $svgFile = $request->file('svg_file');
            $newSvgFileName = Str::slug($request->name) . '-' . time() . '.svg';
            $svgFile->move(public_path('uploads/patterns/svg'), $newSvgFileName);
            $validated['svg_path'] = $newSvgFileName;
        }

        $pattern->update($validated);
        $pattern->tags()->sync($validated['tags'] ?? []);

        // Automatic media record creation for new SVG file (for user-side visibility)
        if ($newSvgFileName) {
            $pattern->media()->create([
                'type' => 'svg',
                'path' => 'patterns/svg/' . $newSvgFileName,
                'alt_text' => $pattern->name . ' pattern',
                'sort_order' => 0,
            ]);
        }

        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $index => $file) {
                $filename = time() . '_' . $index . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/patterns'), $filename);
                
                $pattern->media()->create([
                    'type' => 'image',
                    'path' => 'patterns/' . $filename,
                    'alt_text' => $validated['media_alt'][$index] ?? $pattern->name . ' pattern',
                    'sort_order' => $pattern->media()->max('sort_order') + 1 + $index,
                ]);
            }
        }

        return redirect()->route('admin.patterns.index')->with('success', 'Pattern updated successfully.');
    }

    public function destroy(YakanPattern $pattern)
    {
        foreach ($pattern->media as $media) {
            Storage::disk('public')->delete($media->path);
        }
        $pattern->delete();
        return redirect()->route('admin.patterns.index')->with('success', 'Pattern deleted successfully.');
    }
}
