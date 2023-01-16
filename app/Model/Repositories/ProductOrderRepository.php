<?php

namespace App\Model\Repositories;

/**
 * Class ProductOrderRepository
 * @package App\Model\Repositories
 */

class ProductOrderRepository extends BaseRepository {
    public function findProductOrdersCountByProduct(int $productId): int {
        return $this->connection->select('COUNT(*)')
            ->from('product_order')
            ->where('product_id = %i', $productId)
            ->fetchSingle();
    }
}