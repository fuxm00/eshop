<?php

namespace App\Model\Facades;

use App\Model\Entities\PurchaseOrder;
use App\Model\Repositories\PurchaseOrderRepository;

class PurchaseOrderFacade
{
    private PurchaseOrderRepository $purchaseOrderRepository;

    public function __construct(PurchaseOrderRepository $purchaseOrderRepository){
        $this->purchaseOrderRepository=$purchaseOrderRepository;
    }

    public function savePurchaseOrder(PurchaseOrder $purchaseOrder) {
        return (bool)$this->purchaseOrderRepository->persist($purchaseOrder);
    }
}