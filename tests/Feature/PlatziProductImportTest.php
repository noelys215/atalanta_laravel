<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PlatziProductImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_products_from_platzi()
    {
        Http::fake([
            'https://api.escuelajs.co/api/v1/products*' => Http::response([
                [
                    'id' => 101,
                    'title' => 'Platzi Tee',
                    'description' => 'A tee',
                    'price' => 19.99,
                    'images' => ['https://example.com/tee.jpg'],
                    'category' => ['name' => 'Tops'],
                ],
            ], 200),
        ]);

        Artisan::call('platzi:import-products', ['--limit' => 1]);

        $product = Product::where('external_source', 'platzi')->where('external_id', '101')->first();

        $this->assertNotNull($product);
        $this->assertSame('Platzi Tee', $product->name);
        $this->assertSame('Tops', $product->category);
        $this->assertSame(['https://example.com/tee.jpg'], $product->image);
        $this->assertNull($product->image_path);
    }

    public function test_it_updates_existing_products_by_external_id()
    {
        Http::fakeSequence('https://api.escuelajs.co/api/v1/products*')
            ->push([
                [
                    'id' => 202,
                    'title' => 'Original Name',
                    'description' => 'Original',
                    'price' => 10,
                    'images' => ['https://example.com/original.jpg'],
                    'category' => ['name' => 'Accessories'],
                ],
            ], 200)
            ->push([
                [
                    'id' => 202,
                    'title' => 'Updated Name',
                    'description' => 'Updated',
                    'price' => 12,
                    'images' => ['https://example.com/updated.jpg'],
                    'category' => ['name' => 'Accessories'],
                ],
            ], 200);

        Artisan::call('platzi:import-products', ['--limit' => 1]);
        Artisan::call('platzi:import-products', ['--limit' => 1]);

        $product = Product::where('external_source', 'platzi')->where('external_id', '202')->first();

        $this->assertNotNull($product);
        $this->assertSame('Updated Name', $product->name);
        $this->assertSame(['https://example.com/updated.jpg'], $product->image);
    }

    public function test_it_generates_unique_slugs_for_duplicate_titles()
    {
        Http::fake([
            'https://api.escuelajs.co/api/v1/products*' => Http::response([
                [
                    'id' => 301,
                    'title' => 'Same Title',
                    'description' => 'One',
                    'price' => 10,
                    'images' => ['https://example.com/one.jpg'],
                    'category' => ['name' => 'Tops'],
                ],
                [
                    'id' => 302,
                    'title' => 'Same Title',
                    'description' => 'Two',
                    'price' => 11,
                    'images' => ['https://example.com/two.jpg'],
                    'category' => ['name' => 'Tops'],
                ],
            ], 200),
        ]);

        Artisan::call('platzi:import-products', ['--limit' => 2]);

        $first = Product::where('external_id', '301')->first();
        $second = Product::where('external_id', '302')->first();

        $this->assertSame('same-title', $first->slug);
        $this->assertSame('same-title-2', $second->slug);
    }
}
