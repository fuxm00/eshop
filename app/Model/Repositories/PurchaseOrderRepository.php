<?php

namespace App\Model\Repositories;

/**
 * Class PurchaseOrderRepository
 * @package App\Model\Repositories
 */

class PurchaseOrderRepository extends BaseRepository {
    public function findPurchaseOrdersCountByUser(int $userId): int {
        return $this->connection->select('COUNT(*)')
            ->from('purchase_order')
            ->where('user_id = %i', $userId)
            ->fetchSingle();
    }
}