<?php

namespace App\Model\Entities;

use LeanMapper\Entity;

/**
 * Class Category
 * @package App\Model\Entities
 * @property int $categoryId
 * @property string $title
 * @property string $description
 * @property Product[] $products m:hasMany
 */
class Category extends Entity implements \Nette\Security\Resource {

  /**
   * @inheritDoc
   */
  function getResourceId():string{
    return 'Category';
  }
}