<?php

namespace App\Model\Entities;

use LeanMapper\Entity;

/**
 * Class User
 * @package App\Model\Entities
 * @property int $userId
 * @property string $name
 * @property Role|null $role m:hasOne
 * @property string $email
 * @property string|null $facebookId = null
 * @property string|null $password = null
 * @property string|null $country = null
 * @property string|null $city = null
 * @property string|null $street = null
 * @property int|null $addressNumber = null
 * @property string|null $zip = null
 * @property string|null $telNumber = null
 * @property PurchaseOrder[] $purchaseOrders m:belongsToMany
 */
class User extends Entity{

}