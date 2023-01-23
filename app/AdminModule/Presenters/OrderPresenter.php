<?php

namespace App\AdminModule\Presenters;

use App\Model\Facades\PurchaseOrderFacade;

class OrderPresenter extends BasePresenter {
    private PurchaseOrderFacade $purchaseOrderFacade;

    public function renderDefault(): void {
        $this->template->purchaseOrders = $this->purchaseOrderFacade->findPurchaseOrders(['order' => 'created_at DESC']);
    }

    public function actionChangeState(int $orderId, string $newState): void {
        try {
            $purchaseOrder = $this->purchaseOrderFacade->getPurchaseOrder($orderId);
        } catch (\Exception $e) {
            $this->flashMessage('Požadovaná objednávka nebyla nalezena.', 'error');
            $this->redirect('default');
        }

        if (!$this->user->isAllowed($purchaseOrder,'changeState')) {
            $this->flashMessage('Nemáte oprávnění k změně stavu objednávky nebo už je doručená/zrušená.', 'error');
            $this->redirect('default');
        }

        $this->purchaseOrderFacade->changeState($purchaseOrder, $newState);
        $this->redirect('default');
    }

    #region injections
    public function injectUsersFacade(PurchaseOrderFacade $purchaseOrderFacade): void {
        $this->purchaseOrderFacade = $purchaseOrderFacade;
    }
    #endregion
}

