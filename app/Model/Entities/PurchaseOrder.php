<?php

namespace App\Model\Entities;

use LeanMapper\Entity;
use Dibi\DateTime;

/**
 * Class PurchaseOrder
 * @package App\Model\Entities
 * @property int $orderId
 * @property int $total
 * @property DateTime $createdAt
 * @property string $country
 * @property string $city
 * @property string $street
 * @property int $addressNumber
 * @property string $zip
 * @property string $mail
 * @property string|null $telNumber = null
 * @property string $state = 'pending'
 * @property string $name
 * @property User $user m:hasOne(user_id)
 */

class PurchaseOrder extends Entity implements \Nette\Security\Resource {

    /**
     * @inheritDoc
     */
    function getResourceId():string{
        return 'PurchaseOrder';
    }
}
