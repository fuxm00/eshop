<?php

namespace App\Model\Repositories;

use App\Model\Entities\Category;

/**
 * Class CategoryRepository - repozitář pro kategorie
 * @package App\Model\Repositories
 */
class CategoryRepository extends BaseRepository{
    public function findProductsCount(Category $category): int {
        $query = $this->connection->select('COUNT(*) AS count')
            ->from('category_product')
            ->where('category_id = %i', $category->categoryId);
        return $query->fetchSingle();
    }
}