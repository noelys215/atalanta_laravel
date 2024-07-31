<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    // Fetch all products
    public function getProducts()
    {
        $products = Product::all();
        return response()->json($products);
    }

    // Fetch single product by ID
    public function getProductById($id)
    {
        $product = Product::find($id);

        if ($product) {
            return response()->json($product);
        } else {
            return response()->json(['error' => 'Product Not Found'], 404);
        }
    }

    // Delete a product by ID
    public function deleteProduct($id)
    {
        $product = Product::find($id);

        if ($product) {
            $product->delete();
            return response()->json(['message' => 'Product Removed']);
        } else {
            return response()->json(['error' => 'Product Not Found'], 404);
        }
    }

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
                $path = $file->store('products', 's3');
                Storage::disk('s3')->setVisibility($path, 'public');
                Storage::putFileAs('images', $path, $file);
                $images[] = Storage::disk('s3')->url($path);
            }
        }

        $product = new Product([
            'name' => $request->name,
            'price' => $request->price,
            'category' => $request->category,
            'department' => $request->department,
            'brand' => $request->brand,
            'color' => $request->color,
            'description' => $request->description,
            'inventory' => json_encode($request->inventory),
            'image' => json_encode($images),  // Store URLs directly
            'slug' => Str::slug($request->name),
        ]);

        $product->save();

        return response()->json($product, 201);
    }

    // Update a product by ID
    public function updateProduct(Request $request, $id)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric',
            'category' => 'sometimes|string|max:255',
            'department' => 'sometimes|string|max:255',
            'brand' => 'sometimes|string|max:255',
            'color' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'inventory' => 'sometimes|array',
            'image' => 'sometimes|array',
            'image.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $product = Product::find($id);

        if (!$product) {
            return response()->json(['error' => 'Product Not Found'], 404);
        }

        $images = json_decode($product->image, true);
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $path = $file->store('products', 's3');
                Storage::disk('s3')->setVisibility($path, 'public');
                $images[] = Storage::disk('s3')->url($path);
            }
        }

        $product->update([
            'name' => $request->input('name', $product->name),
            'price' => $request->input('price', $product->price),
            'category' => $request->input('category', $product->category),
            'department' => $request->input('department', $product->department),
            'brand' => $request->input('brand', $product->brand),
            'color' => $request->input('color', $product->color),
            'description' => $request->input('description', $product->description),
            'inventory' => $request->input('inventory', $product->inventory),
            'image' => json_encode($images),  // Store URLs directly
            'slug' => Str::slug($request->input('name', $product->name)),
        ]);

        return response()->json($product);
    }
}
