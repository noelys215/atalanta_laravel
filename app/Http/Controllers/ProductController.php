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
        Log::info('createProduct method called');
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
            Log::info('Creating new product with data: ', $request->all());
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
            Log::info('Product created successfully: ' . $product->id);

            // Navigate back to admin/products
            return redirect()->route('filament.resources.products.index')->with('success', 'Product created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating product: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
