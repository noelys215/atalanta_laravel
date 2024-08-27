<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_product_successfully()
    {
        Storage::fake('s3');

        // Create a user and authenticate them
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/products', [
            'name' => 'Test Product',
            'price' => 99.99,
            'category' => 'Electronics',
            'department' => 'Tech',
            'brand' => 'TestBrand',
            'color' => 'Black',
            'description' => 'A test product',
            'inventory' => [
                ['size' => 'M', 'quantity' => 10],
                ['size' => 'L', 'quantity' => 5],
            ],
            'image' => [
                UploadedFile::fake()->image('product1.jpg'),
                UploadedFile::fake()->image('product2.jpg')
            ],
        ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'slug' => 'test-product',
            'category' => 'Electronics',
            'department' => 'Tech',
            'brand' => 'TestBrand',
        ]);

        Storage::disk('s3')->assertExists('products');
    }


    public function test_get_all_products_without_filters()
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
    }

    public function test_get_all_products_with_search()
    {
        Product::factory()->create(['name' => 'Test Product 1']);
        Product::factory()->create(['name' => 'Another Product']);
        Product::factory()->create(['name' => 'Test Product 2']);

        $response = $this->getJson('/api/products?search=Test');

        $response->assertStatus(200);
        $response->assertJsonCount(2);
    }

    public function test_get_all_products_with_department_filter()
    {
        Product::factory()->create(['department' => 'Tech']);
        Product::factory()->create(['department' => 'Home']);
        Product::factory()->create(['department' => 'Tech']);

        $response = $this->getJson('/api/products?department=Tech');

        $response->assertStatus(200);
        $response->assertJsonCount(2);
    }

    public function test_get_product_by_id_successfully()
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $product->id,
            'name' => $product->name,
        ]);
    }

    public function test_get_product_by_id_not_found()
    {
        $response = $this->getJson('/api/products/9999');

        $response->assertStatus(404);
        $response->assertJson(['error' => 'Product not found']);
    }

    public function test_get_product_by_slug_successfully()
    {
        $product = Product::factory()->create(['slug' => 'test-product']);

        $response = $this->getJson('/api/products/slug/test-product');

        $response->assertStatus(200);
        $response->assertJson([
            'slug' => 'test-product',
            'name' => $product->name,
        ]);
    }

    public function test_get_product_by_slug_not_found()
    {
        $response = $this->getJson('/api/products/slug/non-existent-slug');

        $response->assertStatus(404);
        $response->assertJson(['error' => 'Product not found']);
    }
}
