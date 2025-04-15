<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use MongoDB\Laravel\Eloquent\Model;

class ProductRepository
{
    protected Product $model;

    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    /**
     * Get all products
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Find product by ID
     *
     * @param string $id
     * @return Model|null
     */
    public function find(string $id): ?Model
    {
        return $this->model->find($id);
    }

    /**
     * Create new product
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update product
     *
     * @param string $id
     * @param array $data
     * @return bool
     */
    public function update(string $id, array $data): bool
    {
        $product = $this->find($id);
        if (!$product) {
            return false;
        }
        return $product->update($data);
    }

    /**
     * Delete product
     *
     * @param string $id
     * @return bool
     */
    public function delete(string $id): bool
    {
        $product = $this->find($id);
        if (!$product) {
            return false;
        }
        return $product->delete();
    }
}
