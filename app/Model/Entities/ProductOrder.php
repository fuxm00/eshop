<?php

namespace App\Model\Entities;

use LeanMapper\Entity;

/**
 * Class ProductOrder
 * @package App\Model\Entities
 * @property int $orderId
 * @property int $productId
 * @property int $quantity
 * @property int $price
 * @property Product $product m:hasOne
 * @property PurchaseOrder $order m:hasOne
 */
class ProductOrder extends Entity implements \Nette\Security\Resource {

    /**
     * @inheritDoc
     */
    function getResourceId():string{
        return 'ProductOrder';
    }
}