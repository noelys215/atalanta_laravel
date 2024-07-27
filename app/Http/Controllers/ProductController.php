<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

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
        $product = new Product([
            'name' => 'Sample Name',
            'price' => 0,
            'category' => 'shirts',
            'department' => 'woman',
            'brand' => 'Nike',
            'color' => 'Black',
            'description' => 'N/A',
            'inventory' => json_encode([
                ['quantity' => 5, 'size' => 'XS'],
                ['quantity' => 4, 'size' => 'SM'],
                ['quantity' => 3, 'size' => 'MD'],
            ]),
            'image' => json_encode([
                'https://res.cloudinary.com/dshviljjs/image/upload/v1671069321/Atalanta%20Uploads/STATIC/sample_rpm8f4.jpg',
            ]),
        ]);

        $product->save();

        return response()->json($product, 201);
    }

    // Update a product by ID
    public function updateProduct(Request $request, $id)
    {
        $product = Product::find($id);

        if ($product) {
            $product->name = $request->input('name', $product->name);
            $product->price = $request->input('price', $product->price);
            $product->category = $request->input('category', $product->category);
            $product->department = $request->input('department', $product->department);
            $product->brand = $request->input('brand', $product->brand);
            $product->color = $request->input('color', $product->color);
            $product->description = $request->input('description', $product->description);
            $product->inventory = $request->input('inventory', $product->inventory);
            $product->image = $request->input('image', $product->image);
            $product->slug = $request->input('slug', $product->slug);

            $product->save();

            return response()->json($product);
        } else {
            return response()->json(['error' => 'Product Not Found'], 404);
        }
    }
}
