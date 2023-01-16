<?php

namespace App\Model\Authorizator;

use App\Model\Entities\Category;
use App\Model\Entities\Product;
use App\Model\Entities\Permission;
use App\Model\Entities\PurchaseOrder;
use App\Model\Entities\User;
use App\Model\Facades\ProductOrderFacade;
use App\Model\Facades\PurchaseOrderFacade;
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
    private ProductOrderFacade $productOrderFacade;
    private PurchaseOrderFacade $purchaseOrderFacade;

  /**
   * Metoda pro ověření uživatelských oprávnění
   * @param Role|string|null $role
   * @param Resource|string|null $resource
   * @param string|null $privilege
   * @return bool
   */
  public function isAllowed($role = self::ALL, $resource = self::ALL, $privilege = self::ALL): bool {
    //tady jsou kontroly pro jednotlivé entity
    if ($resource instanceof Category) {
      return $this->categoryResourceIsAllowed($role, $resource, $privilege);
    }

    if ($resource instanceof Product) {
      return $this->productResourceIsAllowed($role, $resource, $privilege);
    }

    if ($resource instanceof User) {
      return $this->userResourceIsAllowed($role, $resource, $privilege);
    }

    if ($resource instanceof PurchaseOrder) {
      return $this->purchaseOrderResourceIsAllowed($role, $resource, $privilege);
    }

    return parent::isAllowed($role, $resource, $privilege);
  }

  private function categoryResourceIsAllowed($role, Category $resource, $privilege): bool {
   $isAllowedByRoleAndAction = parent::isAllowed($role, 'Category', $privilege);
    switch ($privilege) {
      case 'delete':
        return $this->categoriesFacade->findProductsCount($resource) === 0 && $isAllowedByRoleAndAction;
    }

    return $isAllowedByRoleAndAction;
  }

  private function productResourceIsAllowed($role, Product $resource, $privilege): bool {
    $isAllowedByRoleAndAction = parent::isAllowed($role, 'Product', $privilege);
    switch ($privilege) {
      case 'delete':
        return $this->productOrderFacade->findProductOrdersCountByProduct($resource) == 0 && $isAllowedByRoleAndAction;
    }
    return $isAllowedByRoleAndAction;
  }

    private function userResourceIsAllowed($role, User $resource, $privilege): bool {
        $isAllowedByRoleAndAction = parent::isAllowed($role, 'User', $privilege);
        switch ($privilege) {
            case 'delete':
            return $this->purchaseOrderFacade->findPurchaseOrdersCountByUser($resource) == 0 && $isAllowedByRoleAndAction;
        }
        return $isAllowedByRoleAndAction;
    }

    private function purchaseOrderResourceIsAllowed($role, PurchaseOrder $resource, $privilege): bool {
        $isAllowedByRoleAndAction = parent::isAllowed($role, 'PurchaseOrder', $privilege);
        switch ($privilege) {
            case 'changeState':
                return $resource->canStateBeChanged() && $isAllowedByRoleAndAction;
        }
        return $isAllowedByRoleAndAction;
    }

   /**
       * Authorizator constructor - načte kompletní strukturu oprávnění
       * @param UsersFacade $usersFacade
       * @param CategoriesFacade $categoriesFacade
  */
  public function __construct(UsersFacade $usersFacade, CategoriesFacade $categoriesFacade,
                              ProductOrderFacade $productOrderFacade, PurchaseOrderFacade $purchaseOrderFacade) {
    $this->categoriesFacade = $categoriesFacade;
    $this->productOrderFacade = $productOrderFacade;
    $this->purchaseOrderFacade = $purchaseOrderFacade;

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