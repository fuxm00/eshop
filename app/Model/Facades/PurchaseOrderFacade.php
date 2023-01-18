<?php

namespace App\Model\Facades;

use App\Model\Entities\PurchaseOrder;
use App\Model\Entities\User;
use App\Model\Repositories\PurchaseOrderRepository;

class PurchaseOrderFacade {
    private PurchaseOrderRepository $purchaseOrderRepository;

    public function __construct(PurchaseOrderRepository $purchaseOrderRepository){
        $this->purchaseOrderRepository=$purchaseOrderRepository;
    }

    public function savePurchaseOrder(PurchaseOrder $purchaseOrder): bool {
        return (bool)$this->purchaseOrderRepository->persist($purchaseOrder);
    }

    public function deletePurchaseOrder(PurchaseOrder $purchaseOrder): bool {
        return (bool)$this->purchaseOrderRepository->delete($purchaseOrder);
    }

    public function findPurchaseOrdersCountByUser(User $user): int {
        return $this->purchaseOrderRepository->findPurchaseOrdersCountByUser($user->userId);
    }

    public function findPurchaseOrders(array $params = null, int $offset = null, int $limit = null): array {
        return $this->purchaseOrderRepository->findAllBy($params, $offset, $limit);
    }

    public function getPurchaseOrder(int $id): PurchaseOrder {
        return $this->purchaseOrderRepository->find($id);
    }

    public function changeState(PurchaseOrder $purchaseOrder, string $state): bool {
        $purchaseOrder->state = $state;
        return $this->savePurchaseOrder($purchaseOrder);
    }

    public function getPurchaseOrdersByUser(int $userId): array {
        return $this->purchaseOrderRepository->findAllBy(['user_id' => $userId, 'order' => 'created_at DESC']);
    }
}