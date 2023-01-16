<?php

namespace App\Model\Facades;

use App\Model\Entities\ProductOrder;
use App\Model\Repositories\ProductOrderRepository;

class ProductOrderFacade
{
    private ProductOrderRepository $productOrderRepository;

    public function __construct(ProductOrderRepository $productOrderRepository){
        $this->productOrderRepository = $productOrderRepository;
    }

    public function saveProductOrder(ProductOrder $productOrder) {
        return (bool)$this->productOrderRepository->persist($productOrder);
    }
}