<?php

namespace App\Model\Facades;

use App\Model\Entities\Product;
use App\Model\Repositories\ProductRepository;

class ProductsFacade {
    private ProductRepository $productsRepository;

    public function __construct(ProductRepository $productsRepository){
        $this->productsRepository=$productsRepository;
    }

    public function getProduct(int $id): Product {
        return $this->productsRepository->find($id);
    }

    public function findProducts(array $params = null, int $offset = null, int $limit = null): array {
        return $this->productsRepository->findAllBy($params,$offset,$limit);
    }

    public function saveProduct(Product &$product): bool {
        return (bool)$this->productsRepository->persist($product);
    }

    public function deleteProduct(Product $product): bool {
        try {
            return $this->productsRepository->deleteProduct($product);
        } catch (\Exception $e) {
            return false;
        }
    }

}