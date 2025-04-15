<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Jobs\SendPriceChangeNotification;

class ProductService
{
    public function getAllProducts()
    {
        return Product::all();
    }

    public function findProduct(int $id): ?Product
    {
        return Product::findOrFail($id);
    }

    public function createProduct(array $data, ?UploadedFile $image = null): Product
    {
        $product = Product::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price']
        ]);

        if ($image) {
            $product->image = $this->handleImageUpload($image);
            $product->save();
        }

        return $product;
    }

    public function updateProduct(Product $product, array $data, ?UploadedFile $image = null): Product
    {
        $oldPrice = $product->price;

        $product->fill([
            'name' => $data['name'],
            'description' => $data['description'] ?? $product->description,
            'price' => $data['price'] ?? $product->price
        ]);

        if ($image) {
            $this->deleteOldImage($product->image);
            $product->image = $this->handleImageUpload($image);
        }

        $product->save();

        if ($oldPrice != $product->price) {
            $this->notifyPriceChange($product, $oldPrice);
        }

        return $product;
    }

    public function deleteProduct(Product $product): void
    {
        $this->deleteOldImage($product->image);
        $product->delete();
    }

    private function handleImageUpload(UploadedFile $file): string
    {
        $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('public/uploads', $filename);
        return Storage::url($path);
    }

    private function deleteOldImage(?string $imagePath): void
    {
        if ($imagePath && $imagePath !== 'product-placeholder.jpg') {
            Storage::delete(str_replace('/storage', 'public', $imagePath));
        }
    }

    private function notifyPriceChange(Product $product, float $oldPrice): void
    {
        try {
            SendPriceChangeNotification::dispatch(
                $product,
                $oldPrice,
                $product->price,
                config('app.price_notification_email', 'admin@example.com')
            );
        } catch (\Exception $e) {
            Log::error('Failed to dispatch price change notification: ' . $e->getMessage());
        }
    }
}
