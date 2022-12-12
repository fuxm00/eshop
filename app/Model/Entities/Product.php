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
 * @property string $url
 * @property bool $available = true
 * @property string $photoExtension = ''
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

    /**
     * @inheritDoc
     */
    function getResourceUrl():string{
        return '/img/products/'. $this->productId . '.' . $this->photoExtension;
    }
}
