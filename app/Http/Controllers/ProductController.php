<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    // Create a new product
    public function createProduct(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'category' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'color' => 'required|string|max:255',
            'description' => 'nullable|string',
            'inventory' => 'required|array',
            'image' => 'required|array',
            'image.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $images = [];
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                try {
                    $path = $file->store('products', 's3');
                    Storage::disk('s3')->setVisibility($path, 'public');

                    $url = Storage::disk('s3')->url($path);
                    $images[] = $url;

                } catch (\Exception $e) {
                    Log::error('Error uploading image to S3: ' . $e->getMessage());
                    return response()->json(['error' => $e->getMessage()], 500);
                }
            }
        }

        try {
            $product = new Product([
                'name' => $request->name,
                'price' => $request->price,
                'category' => $request->category,
                'department' => $request->department,
                'brand' => $request->brand,
                'color' => $request->color,
                'description' => $request->description,
                'inventory' => $request->inventory,
                'image' => $images,  // Store URLs directly
                'slug' => Str::slug($request->name),
            ]);

            $product->save();

            // Navigate back to admin/products
            return redirect()->route('filament.resources.products.index')->with('success', 'Product created successfully.');
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    // Get all products with search functionality
    public function getProducts(Request $request)
    {
        try {
            $query = Product::query();

            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('category', 'like', '%' . $search . '%')
                        ->orWhere('brand', 'like', '%' . $search . '%');
                });
            }

            // Apply department filter
            if ($request->has('department')) {
                $department = $request->input('department');
                $query->where('department', $department);
            }

            if ($request->has('category')) {
                $category = $request->input('category');
                $query->where('category', $category);
            }



            $products = $query->get();

            return response()->json($products);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error fetching products'], 500);
        }
    }

    // Get a single product by ID
    public function getProductById($id)
    {
        try {
            $product = Product::find($id);
            if ($product) {
                return response()->json($product);
            } else {
                return response()->json(['error' => 'Product not found'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error fetching product'], 500);
        }
    }

    // Get a single product by slug
    public function getProductBySlug($slug)
    {
        try {
            $product = Product::where('slug', $slug)->first();
            if ($product) {
                return response()->json($product);
            } else {
                return response()->json(['error' => 'Product not found'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error fetching product'], 500);
        }
    }

}


