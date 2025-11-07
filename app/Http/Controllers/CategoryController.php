<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories
     */
    public function index()
    {
        $categories = Category::with(['parent', 'children'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category
     */
    public function create()
    {
        $parentCategories = Category::whereNull('parent_id')
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return view('admin.categories.create', compact('parentCategories'));
    }

    /**
     * Store a newly created category with optimized images
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'parent_id' => 'nullable|exists:categories,id',
            'status' => 'required|boolean',
            'icon' => 'nullable|string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120', // 5MB max
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        $validated = $request->validated();

        // Generate slug
        $validated['slug'] = Str::slug($validated['name']);

        // Handle image upload and optimization
        if ($request->hasFile('image')) {
            $optimizedImages = \App\Helpers\ImageOptimizer::optimizeUploadedImage(
                $request->file('image'), 
                'categories',
                [
                    'quality' => 85,
                    'maxWidth' => 800,
                    'maxHeight' => 800,
                    'generateWebP' => true,
                    'generateThumbnails' => true,
                    'thumbnailSizes' => [100, 200, 400]
                ]
            );
            $validated['image'] = $optimizedImages['optimized'];
        }

        // Set default sort order if not provided
        if (!isset($validated['sort_order'])) {
            $validated['sort_order'] = Category::max('sort_order') + 1;
        }

        $category = Category::create($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully with optimized image!');
    }

    /**
     * Display the specified category
     */
    public function show(Category $category)
    {
        $category->load(['parent', 'children', 'products' => function($query) {
            $query->where('status', 'active')->take(12);
        }]);

        return view('admin.categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified category
     */
    public function edit(Category $category)
    {
        $parentCategories = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * Update the specified category with optimized images
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'parent_id' => 'nullable|exists:categories,id',
            'status' => 'required|boolean',
            'icon' => 'nullable|string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        $validated = $request->validated();
        $validated['slug'] = Str::slug($validated['name']);

        // Handle image replacement
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
                
                // Also delete generated thumbnails and WebP versions if they exist
                $pathInfo = pathinfo($category->image);
                $baseName = $pathInfo['filename'];
                $directory = $pathInfo['dirname'];
                
                // Clean up thumbnails and WebP versions
                $filesToDelete = [
                    $directory . '/' . $baseName . '.webp',
                    $directory . '/' . $baseName . '_100.' . $pathInfo['extension'],
                    $directory . '/' . $baseName . '_200.' . $pathInfo['extension'],
                    $directory . '/' . $baseName . '_400.' . $pathInfo['extension'],
                ];
                
                foreach ($filesToDelete as $file) {
                    if (Storage::disk('public')->exists($file)) {
                        Storage::disk('public')->delete($file);
                    }
                }
            }

            $optimizedImages = \App\Helpers\ImageOptimizer::optimizeUploadedImage(
                $request->file('image'), 
                'categories',
                [
                    'quality' => 85,
                    'maxWidth' => 800,
                    'maxHeight' => 800,
                    'generateWebP' => true,
                    'generateThumbnails' => true,
                    'thumbnailSizes' => [100, 200, 400]
                ]
            );
            $validated['image'] = $optimizedImages['optimized'];
        }

        $category->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully!');
    }

    /**
     * Remove the specified category
     */
    public function destroy(Category $category)
    {
        // Check if category has products
        if ($category->products()->count() > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Cannot delete category with existing products. Please move or delete products first.');
        }

        // Check if category has subcategories
        if ($category->children()->count() > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Cannot delete category with subcategories. Please move or delete subcategories first.');
        }

        // Delete associated image files
        if ($category->image && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
            
            // Also delete generated thumbnails and WebP versions
            $pathInfo = pathinfo($category->image);
            $baseName = $pathInfo['filename'];
            $directory = $pathInfo['dirname'];
            
            $filesToDelete = [
                $directory . '/' . $baseName . '.webp',
                $directory . '/' . $baseName . '_100.' . $pathInfo['extension'],
                $directory . '/' . $baseName . '_200.' . $pathInfo['extension'],
                $directory . '/' . $baseName . '_400.' . $pathInfo['extension'],
            ];
            
            foreach ($filesToDelete as $file) {
                if (Storage::disk('public')->exists($file)) {
                    Storage::disk('public')->delete($file);
                }
            }
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully!');
    }

    /**
     * AJAX upload for category image preview
     */
    public function uploadImagePreview(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
        ]);

        try {
            $optimizedImages = \App\Helpers\ImageOptimizer::optimizeUploadedImage(
                $request->file('image'), 
                'temp/categories',
                [
                    'quality' => 85,
                    'maxWidth' => 800,
                    'maxHeight' => 800,
                    'generateWebP' => true,
                    'generateThumbnails' => true,
                    'thumbnailSizes' => [100, 200]
                ]
            );

            return response()->json([
                'success' => true,
                'image_url' => Storage::url($optimizedImages['optimized']),
                'thumbnail_url' => Storage::url($optimizedImages['thumbnails'][200] ?? $optimizedImages['optimized']),
                'file_path' => $optimizedImages['optimized']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload and optimize image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder categories (AJAX)
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:categories,id',
            'categories.*.sort_order' => 'required|integer|min:0',
        ]);

        try {
            foreach ($request->categories as $categoryData) {
                Category::where('id', $categoryData['id'])
                    ->update(['sort_order' => $categoryData['sort_order']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Categories reordered successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder categories: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get categories for frontend (Public)
     */
    public function getCategories(Request $request)
    {
        $query = Category::active()
            ->with(['children' => function($query) {
                $query->active()->orderBy('sort_order')->orderBy('name');
            }])
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($request->has('with_products_count')) {
            $query->withCount('products');
        }

        $categories = $query->get();

        return response()->json([
            'success' => true,
            'categories' => $categories
        ]);
    }

    /**
     * Show category products page (Frontend)
     */
    public function showProducts($slug)
    {
        $category = Category::where('slug', $slug)
            ->where('status', true)
            ->firstOrFail();

        // Get all descendant categories
        $categoryIds = $this->getAllCategoryIds($category);

        $products = \App\Models\Product::whereIn('category_id', $categoryIds)
            ->where('status', 'active')
            ->with(['category', 'reviews'])
            ->paginate(12);

        return view('categories.show', compact('category', 'products'));
    }

    /**
     * Get all category IDs including descendants
     */
    private function getAllCategoryIds(Category $category): array
    {
        $ids = [$category->id];
        
        foreach ($category->children as $child) {
            $ids = array_merge($ids, $this->getAllCategoryIds($child));
        }
        
        return $ids;
    }
}