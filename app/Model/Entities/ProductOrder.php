<?php

namespace App\Model\Entities;

use LeanMapper\Entity;

/**
 * Class ProductOrder
 * @package App\Model\Entities
 * @property int $productOrderId
 * @property PurchaseOrder $purchaseOrder m:hasOne
 * @property Product $product m:hasOne
 * @property int $price
 * @property int $quantity
 */
class ProductOrder extends Entity implements \Nette\Security\Resource {

    /**
     * @inheritDoc
     */
    function getResourceId():string{
        return 'ProductOrder';
    }
}