<?php

namespace App\Model\Repositories;

use App\Model\Entities\Product;

/**
 * Class ProductRepository
 * @package App\Model\Repositories
 */

class ProductRepository extends BaseRepository {
    public function deleteProduct(Product $product): bool {
        $this->connection->delete('category_product')
            ->where('product_id = %i', $product->productId)->execute();

        return (bool)$this->connection->delete('product')
            ->where('product_id = %i', $product->productId)
            ->execute();
    }
}