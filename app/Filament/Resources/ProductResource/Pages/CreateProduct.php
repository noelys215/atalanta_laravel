<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Http\Controllers\ProductController;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Route;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function handleRecordCreate(array $data): Model
    {
        // Create product using ProductController
        $productController = new ProductController();
        $request = new \Illuminate\Http\Request();
        $request->replace($data);

        return $productController->createProduct($request);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
