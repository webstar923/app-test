<?php

namespace App\Services;

use App\Jobs\SendPriceChangeNotification;
use App\Repositories\ProductRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use MongoDB\Laravel\Eloquent\Model;

class ProductService
{
    protected ProductRepository $repository;

    public function __construct(ProductRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all products
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllProducts()
    {
        return $this->repository->all();
    }

    /**
     * Create a new product
     *
     * @param array $data
     * @param UploadedFile|null $image
     * @return Model
     */
    public function createProduct(array $data, ?UploadedFile $image = null): Model
    {
        if ($image) {
            $data['image'] = $this->handleImageUpload($image);
        } else {
            $data['image'] = 'product-placeholder.jpg';
        }

        return $this->repository->create($data);
    }

    /**
     * Update a product
     *
     * @param string $id
     * @param array $data
     * @param UploadedFile|null $image
     * @return bool
     */
    public function updateProduct(string $id, array $data, ?UploadedFile $image = null): bool
    {
        $product = $this->repository->find($id);
        if (!$product) {
            return false;
        }

        $oldPrice = $product->price;

        if ($image) {
            $data['image'] = $this->handleImageUpload($image);
            // Delete old image if it exists and is not the placeholder
            if ($product->image && $product->image !== 'product-placeholder.jpg') {
                Storage::delete($product->image);
            }
        }

        $updated = $this->repository->update($id, $data);

        if ($updated && isset($data['price']) && $oldPrice != $data['price']) {
            $this->notifyPriceChange($product, $oldPrice, $data['price']);
        }

        return $updated;
    }

    /**
     * Delete a product
     *
     * @param string $id
     * @return bool
     */
    public function deleteProduct(string $id): bool
    {
        $product = $this->repository->find($id);
        if (!$product) {
            return false;
        }

        // Delete product image if it exists and is not the placeholder
        if ($product->image && $product->image !== 'product-placeholder.jpg') {
            Storage::delete($product->image);
        }

        return $this->repository->delete($id);
    }

    /**
     * Handle image upload
     *
     * @param UploadedFile $image
     * @return string
     */
    protected function handleImageUpload(UploadedFile $image): string
    {
        $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
        $path = $image->storeAs('uploads', $filename, 'public');
        return $path;
    }

    /**
     * Notify about price change
     *
     * @param Model $product
     * @param float $oldPrice
     * @param float $newPrice
     * @return void
     */
    protected function notifyPriceChange(Model $product, float $oldPrice, float $newPrice): void
    {
        try {
            SendPriceChangeNotification::dispatch(
                $product,
                $oldPrice,
                $newPrice,
                config('app.price_notification_email')
            );
        } catch (\Exception $e) {
            Log::error('Failed to dispatch price change notification: ' . $e->getMessage());
        }
    }
}
