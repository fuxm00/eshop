<?php

namespace App\Model\Authorizator;

use App\Model\Entities\Category;
use App\Model\Entities\Product;
use App\Model\Entities\Permission;
use App\Model\Facades\UsersFacade;
use App\Model\Facades\CategoriesFacade;
use Nette\Security\Resource;
use Nette\Security\Role;

/**
 * Class Authorizator
 * @package App\Model\Authorization
 */
class Authorizator extends \Nette\Security\Permission {

    private CategoriesFacade $categoriesFacade;

  /**
   * Metoda pro ověření uživatelských oprávnění
   * @param Role|string|null $role
   * @param Resource|string|null $resource
   * @param string|null $privilege
   * @return bool
   */
  public function isAllowed($role = self::ALL, $resource = self::ALL, $privilege = self::ALL): bool {
    //tady mohou být kontroly pro jednotlivé entity
    if ($resource instanceof Category){
      return $this->categoryResourceIsAllowed($role, $resource, $privilege);
    }

    if ($resource instanceof Product){
      return $this->productResourceIsAllowed($role, $resource, $privilege);
    }

    return parent::isAllowed($role, $resource, $privilege);
  }

  private function categoryResourceIsAllowed($role, Category $resource, $privilege): bool {
    switch ($privilege) {
      case 'delete':
        return $this->categoriesFacade->findProductsCount($resource) === 0;
    }

    return parent::isAllowed($role, 'Category', $privilege);
  }

  private function productResourceIsAllowed($role, Product $resource, $privilege): bool {
    switch ($privilege){
      case 'delete':
        //TODO kontrola, jestli je produkt v nějaké objednávce
    }
    return parent::isAllowed($role, 'Product', $privilege);
  }

   /**
       * Authorizator constructor - načte kompletní strukturu oprávnění
       * @param UsersFacade $usersFacade
       * @param CategoriesFacade $categoriesFacade
  */
  public function __construct(UsersFacade $usersFacade, CategoriesFacade $categoriesFacade) {
    $this->categoriesFacade = $categoriesFacade;
    foreach ($usersFacade->findResources() as $resource){
      $this->addResource($resource->resourceId);
    }

    foreach ($usersFacade->findRoles() as $role){
      $this->addRole($role->roleId);
    }

    foreach ($usersFacade->findPermissions() as $permission){
      if ($permission->type==Permission::TYPE_ALLOW){
        $this->allow($permission->roleId,$permission->resourceId,$permission->action?$permission->action:null);
      }else{
        $this->deny($permission->roleId,$permission->resourceId,$permission->action?$permission->action:null);
      }
    }
  }

}