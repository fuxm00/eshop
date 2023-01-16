<?php

namespace App\FrontModule\Presenters;

use App\FrontModule\Components\UserDetailsForm\UserDetailsForm;
use App\FrontModule\Components\UserDetailsForm\UserDetailsFormFactory;
use App\Model\Entities\Cart;
use App\Model\Entities\CartItem;
use App\Model\Entities\Product;
use App\Model\Entities\ProductOrder;
use App\Model\Entities\PurchaseOrder;
use App\Model\Facades\ProductOrderFacade;
use App\Model\Facades\ProductsFacade;
use App\Model\Facades\PurchaseOrderFacade;
use App\Model\Facades\CartFacade;
use App\Model\Facades\UsersFacade;
use Dibi\DateTime;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use Nette\Security\User;

class CartPresenter extends BasePresenter {

    private UserDetailsFormFactory $userDetailsFormFactory;
    private PurchaseOrderFacade $purchaseOrderFacade;
    private UsersFacade $usersFacade;
    private CartFacade $cartFacade;
    private ProductOrderFacade $productOrderFacade;
    private ProductsFacade $productsFacade;

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
            $purchaseOrder->telNumber = $values['telNumber'];
            $purchaseOrder->addressNumber = $values['addressNumber'];
            $purchaseOrder->createdAt = new DateTime();
            $purchaseOrder->name = $values['name'];
            $purchaseOrder->mail = $values['email'];
            if ($this->user->getIdentity() != null) {
                $purchaseOrder->user = $this->usersFacade->getUserByEmail($this->user->getIdentity()->email);
            }
            //TODO total nefunguje, když uživatel není přihlášenej
            $purchaseOrder->total = (int)$this->cartFacade->getCartByUser($this->user->id)->getTotalPrice();

            try {
                $this->purchaseOrderFacade->savePurchaseOrder($purchaseOrder);
            } catch (\Exception $e) {
                $this->flashMessage('Nastala chyba při odesílání objednávky.', 'error');
                $this->redirect('Cart:default');
            }


            $cartItems = $this->cartFacade->getCartByUser($this->user->id)->items;
            foreach ($cartItems as $cartItem) {
                $productOrder = new ProductOrder();
                //TODO kvantita
                $productOrder->quantity = $cartItem->count;
                $productOrder->price = (int)$cartItem->product->price;
                $productOrder->product = $cartItem->product;
                $productOrder->order = $purchaseOrder;


                $this->productOrderFacade->saveProductOrder($productOrder);
                /*
                try {
                    $this->productOrderFacade->saveProductOrder($productOrder);
                } catch (\Exception $e) {
                    $this->flashMessage('Nastala chyba při odesílání objednávky.', 'error');
                    $this->redirect('Cart:default');
                }*/
            }

            //TODO vymazat obsah košíku

            $this->flashMessage('Objednávka odeslána.');
            $this->redirect('Cart:default');
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

    public function injectProductsFacade(ProductsFacade $productsFacade) {
        $this->productsFacade = $productsFacade;
    }
}