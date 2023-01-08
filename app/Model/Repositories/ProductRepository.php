<?php

namespace App\Model\Repositories;

/**
 * Class ProductRepository
 * @package App\Model\Repositories
 */

class ProductRepository extends BaseRepository {
    public function findAllBy($whereArr = null, $offset = null, $limit = null): array {
        $query = $this->connection->select('*')->from($this->getTable())
            ->leftJoin(('category_product'))
            ->using('(product_id)');
        if (isset($whereArr['order'])) {
            $query->orderBy($whereArr['order']);
        }
        if (isset($whereArr['categories'])) {
            $query->where('category_id IN %in', $whereArr['categories']);
        }
        if (isset($whereArr['onlyAvailable'])) {
            $query->where('available = 1');
        }
        return $this->createEntities(
            $query->fetchAll($offset, $limit)
        );
    }
}