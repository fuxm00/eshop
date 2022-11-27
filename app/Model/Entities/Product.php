<?php

namespace App\Model\Entities;

use LeanMapper\Entity;

/**
 * Class Product
 * @package App\Model\Entities
 * @property int $productId
 * @property string $name
 * @property string|null $description = null
 * @property float $price
 * @property Category[] $categories m:hasMany(:category_product::)
 *
 * @method bool removeAllCategories()
 * @method bool addToCategories(Category $category)
 */

class Product extends Entity implements \Nette\Security\Resource {

    /**
     * @inheritDoc
     */
    function getResourceId():string{
        return 'Product';
    }
}
