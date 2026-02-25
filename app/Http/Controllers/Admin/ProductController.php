<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Services\CloudinaryService;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource with search and filter.
     */
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Status filter — only apply if explicitly set
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Category filter
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('name', $request->category);
            });
        }

        // Stock level filter
        if ($request->filled('stock')) {
            switch ($request->stock) {
                case 'in_stock':
                    $query->where('stock', '>', 10);
                    break;
                case 'low_stock':
                    $query->where('stock', '>', 0)->where('stock', '<=', 10);
                    break;
                case 'out_of_stock':
                    $query->where('stock', '<=', 0);
                    break;
            }
        }

        $products = $query->latest()->paginate(10);

        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = \App\Models\Category::orderBy('name')->get();
        return view('admin.products.create', compact('categories'));
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        // Validate input
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'image_colors' => 'nullable|array',
            'image_colors.*' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'category_id' => 'nullable|exists:categories,id',
            'available_sizes' => 'nullable|json',
            'available_colors' => 'nullable|json',
        ]);

        // Handle multiple image uploads with color associations
        $imagePath = null;
        $allImages = [];
        $imageColors = $request->input('image_colors', []);
        $cloudinary = new CloudinaryService();
        
        if ($request->hasFile('images')) {
            $imageIndex = 0;
            
            foreach ($request->file('images') as $index => $image) {
                if ($image && $image->isValid()) {
                    $storedPath = null;
                    
                    // Try Cloudinary first (production)
                    if ($cloudinary->isEnabled()) {
                        $result = $cloudinary->uploadFile($image, 'products');
                        if ($result) {
                            $storedPath = $result['url'];
                        }
                    }
                    
                    // Fallback to local storage
                    if (!$storedPath) {
                        $uploadDir = public_path('uploads/products');
                        if (!is_dir($uploadDir)) {
                            @mkdir($uploadDir, 0777, true);
                        }
                        $imageName = time() . '_' . $imageIndex . '_' . $image->getClientOriginalName();
                        $image->move($uploadDir, $imageName);
                        $storedPath = $imageName;
                    }
                    
                    $allImages[] = [
                        'path' => $storedPath,
                        'color' => $imageColors[$index] ?? null,
                        'sort_order' => $imageIndex
                    ];
                    
                    // First VALID image becomes the main image
                    if ($imagePath === null) {
                        $imagePath = $storedPath;
                    }
                    
                    $imageIndex++;
                }
            }
        }

        // Parse sizes and colors JSON
        $sizes = $request->available_sizes ? json_decode($request->available_sizes, true) : null;
        $colors = $request->available_colors ? json_decode($request->available_colors, true) : null;

        // Create product
        $product = Product::create([
            'name' => $request->name,
            'price' => $request->price,
            'stock' => $request->stock,
            'description' => $request->description,
            'image' => $imagePath,
            'status' => $request->status,
            'category_id' => $request->category_id,
            'available_sizes' => $sizes,
            'available_colors' => $colors,
        ]);
        
        // Store all images with color associations in JSON column
        if (!empty($allImages)) {
            $product->update(['all_images' => json_encode($allImages)]);
        }

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully with ' . count($allImages) . ' image(s).');
    }

    /**
     * Display the specified product.
     */
    public function show(string $id)
    {
        $product = Product::with('inventory')->findOrFail($id);
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(string $id)
    {
        $product = Product::with('inventory')->findOrFail($id);
        $categories = Category::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        // Validate input
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'image_colors' => 'nullable|array',
            'image_colors.*' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'category_id' => 'nullable|exists:categories,id',
            'available_sizes' => 'nullable|json',
            'available_colors' => 'nullable|json',
            'delete_images' => 'nullable|json',
        ]);

        // Handle image deletions
        $allImages = $product->all_images ?? [];
        // Ensure $allImages is an array
        if (is_string($allImages)) {
            $allImages = json_decode($allImages, true) ?? [];
        }
        
        $imagesToDelete = $request->delete_images ? json_decode($request->delete_images, true) : [];
        $cloudinary = new CloudinaryService();
        
        if (!empty($imagesToDelete)) {
            // Remove deleted images from array and delete files
            $allImages = array_filter($allImages, function($img) use ($imagesToDelete, $cloudinary) {
                if (in_array($img['path'], $imagesToDelete)) {
                    // Delete from Cloudinary or local
                    if (str_contains($img['path'], 'cloudinary.com')) {
                        // Extract public_id from Cloudinary URL for deletion
                        if (preg_match('/\/upload\/(?:v\d+\/)?(.+?)(?:\.[a-z]+)?$/', $img['path'], $matches)) {
                            $cloudinary->delete($matches[1]);
                        }
                    } else {
                        $filePath = public_path('uploads/products/' . $img['path']);
                        if (file_exists($filePath)) {
                            @unlink($filePath);
                        }
                    }
                    return false;
                }
                return true;
            });
            // Reindex array
            $allImages = array_values($allImages);
        }

        // Handle new image uploads with color associations
        $imagePath = $product->image;
        $imageColors = $request->input('image_colors', []);
        $firstNewImage = null;
        
        if ($request->hasFile('images')) {
            $imageIndex = count($allImages);
            
            foreach ($request->file('images') as $index => $image) {
                if ($image && $image->isValid()) {
                    $storedPath = null;
                    
                    // Try Cloudinary first (production)
                    if ($cloudinary->isEnabled()) {
                        $result = $cloudinary->uploadFile($image, 'products');
                        if ($result) {
                            $storedPath = $result['url'];
                        }
                    }
                    
                    // Fallback to local storage
                    if (!$storedPath) {
                        $uploadDir = public_path('uploads/products');
                        if (!is_dir($uploadDir)) {
                            @mkdir($uploadDir, 0777, true);
                        }
                        $imageName = time() . '_' . $imageIndex . '_' . $image->getClientOriginalName();
                        $image->move($uploadDir, $imageName);
                        $storedPath = $imageName;
                    }
                    
                    $allImages[] = [
                        'path' => $storedPath,
                        'color' => $imageColors[$index] ?? null,
                        'sort_order' => $imageIndex
                    ];
                    
                    // Track first new image
                    if ($firstNewImage === null) {
                        $firstNewImage = $storedPath;
                    }
                    
                    $imageIndex++;
                }
            }
            
            // Update main image to first new image if new images were uploaded
            if ($firstNewImage !== null) {
                $imagePath = $firstNewImage;
            }
        }
        
        // If main image was deleted and no new images, set first remaining image as main
        if (!empty($allImages) && (in_array($imagePath, $imagesToDelete) || !$imagePath)) {
            $imagePath = $allImages[0]['path'];
        }

        // Parse sizes and colors JSON
        $sizes = $request->available_sizes ? json_decode($request->available_sizes, true) : null;
        $colors = $request->available_colors ? json_decode($request->available_colors, true) : null;

        // Update product
        $product->update([
            'name' => $request->name,
            'price' => $request->price,
            'stock' => $request->stock,
            'description' => $request->description,
            'status' => $request->status,
            'category_id' => $request->category_id,
            'image' => $imagePath,
            'available_sizes' => $sizes,
            'available_colors' => $colors,
            'all_images' => !empty($allImages) ? json_encode($allImages) : null,
        ]);

        $imageCount = count($allImages);
        $deletedCount = count($imagesToDelete);
        $message = "Product updated successfully";
        if ($imageCount > 0) {
            $message .= " with {$imageCount} image(s)";
        }
        if ($deletedCount > 0) {
            $message .= ". Deleted {$deletedCount} image(s)";
        }

        return redirect()->route('admin.products.index')->with('success', $message . '.');
    }

    /**
     * Remove the specified product.
     */
    public function destroy(string $id)
    {
        try {
            $product = Product::findOrFail($id);
            $productName = $product->name;

            // Delete associated image if exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            // Delete related records (optional - depends on your foreign key constraints)
            // If you have cascade delete set up in migrations, this is automatic
            // Otherwise, manually delete related records:
            // $product->orderItems()->delete();
            // $product->reviews()->delete();
            // $product->inventory()->delete();

            // Delete the product
            $product->delete();

            // Check if request expects JSON (AJAX request)
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Product '{$productName}' has been deleted successfully."
                ]);
            }

            return redirect()->route('admin.products.index')
                           ->with('success', "Product '{$productName}' deleted successfully.");
                           
        } catch (\Exception $e) {
            \Log::error('Product deletion error: ' . $e->getMessage());
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete product. ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('admin.products.index')
                           ->with('error', 'Failed to delete product.');
        }
    }
}
