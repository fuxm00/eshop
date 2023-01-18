<?php

namespace App\FrontModule\Presenters;

use App\FrontModule\Components\UserDetailsForm\UserDetailsForm;
use App\FrontModule\Components\UserDetailsForm\UserDetailsFormFactory;
use App\Model\Entities\ProductOrder;
use App\Model\Entities\PurchaseOrder;
use App\Model\Facades\ProductOrderFacade;
use App\Model\Facades\PurchaseOrderFacade;
use App\Model\Facades\CartFacade;
use App\Model\Facades\UsersFacade;

class CartPresenter extends BasePresenter {

    private UserDetailsFormFactory $userDetailsFormFactory;
    private PurchaseOrderFacade $purchaseOrderFacade;
    private UsersFacade $usersFacade;
    private CartFacade $cartFacade;
    private ProductOrderFacade $productOrderFacade;

    public function renderDefault(): void {
        $this->template->cart = $this->cartFacade->getCartById($this->getSession()->getSection('cart')->get('cartId'));
    }

    protected function createComponentUserDetailsForm(): UserDetailsForm {
        $form = $this->userDetailsFormFactory->create();
        $form->createSubcomponents(true);

        if ($this->user->getIdentity() != null) {
            $form->prepareDefaults($this->user->getIdentity());
        }

        $form->onSave[] = function () use($form) {

            $values = $form->getValues('array');

            $purchaseOrder = new PurchaseOrder();
            $purchaseOrder->country = $values['country'];
            $purchaseOrder->city = $values['city'];
            $purchaseOrder->street = $values['street'];
            $purchaseOrder->zip = $values['zip'];
            $purchaseOrder->telNumber = empty($values['telNumber']) ? null : $values['telNumber'];
            $purchaseOrder->addressNumber = $values['addressNumber'];
            $purchaseOrder->name = $values['name'];
            $purchaseOrder->mail = $values['email'];

            if ($this->user->isLoggedIn()) {
                $purchaseOrder->user = $this->usersFacade->getUserByEmail($this->user->getIdentity()->email);
            }

            try {
                $cartId = $this->getSession()->getSection('cart')->get('cartId');
                $cart = $this->cartFacade->getCartById($cartId);
                $totalPrice = $cart->getTotalPrice();
                if ($totalPrice <= 0) {
                    throw new \Exception('Košík je prázdný');
                }
            } catch (\Exception $e) {
                $this->flashMessage('Košík je prázdný nebo nebyl nalezen jeho obsah.', 'danger');
                $this->redirect('Product:list');
            }

            $purchaseOrder->total = $totalPrice;

            try {
                $this->purchaseOrderFacade->savePurchaseOrder($purchaseOrder);
            } catch (\Exception $e) {
                $this->flashMessage('Nastala chyba při odesílání objednávky.', 'error');
                $this->redirect('Product:list');
            }

            foreach ($cart->items as $cartItem) {
                $productOrder = new ProductOrder();
                $productOrder->quantity = $cartItem->count;
                $productOrder->price = (int)$cartItem->product->price;
                $productOrder->product = $cartItem->product;
                $productOrder->purchaseOrder = $purchaseOrder;

                try {
                    $this->productOrderFacade->saveProductOrder($productOrder);
                } catch (\Exception $e) {
                    $this->purchaseOrderFacade->deletePurchaseOrder($purchaseOrder);
                    $this->flashMessage('Nastala chyba při odesílání objednávky.', 'error');
                    $this->redirect('Cart:default');
                }
            }

            $this->getSession()->getSection('cart')->remove('cartId');
            $this->cartFacade->deleteCartById($cartId);

            $this->flashMessage('Objednávka odeslána.');
            $this->redirect('Product:list');
        };

        return $form;
    }

    public function injectUserDetailsFormFactory(UserDetailsFormFactory $userDetailsFormFactory): void {
        $this->userDetailsFormFactory=$userDetailsFormFactory;
    }

    public function injectPurchaseOrderFacade(PurchaseOrderFacade $purchaseOrderFacade):void {
        $this->purchaseOrderFacade=$purchaseOrderFacade;
    }

    public function injectUsersFacade(UsersFacade $usersFacade):void {
        $this->usersFacade=$usersFacade;
    }

    public function injectCartFacade(CartFacade $cartFacade) {
        $this->cartFacade = $cartFacade;
    }

    public function injectProductOrderFacade(ProductOrderFacade $productOrderFacade) {
        $this->productOrderFacade = $productOrderFacade;
    }
}