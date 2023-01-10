<?php

namespace App\FrontModule\Presenters;

use App\FrontModule\Components\CartControl\CartControl;
use App\FrontModule\Components\ProductCartForm\ProductCartForm;
use App\FrontModule\Components\ProductCartForm\ProductCartFormFactory;
use App\FrontModule\Components\ProductsFilterForm\ProductsFilterForm;
use App\FrontModule\Components\ProductsFilterForm\ProductsFilterFormFactory;
use App\Model\Facades\ProductsFacade;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Multiplier;

/**
 * Class ProductPresenter
 * @package App\FrontModule\Presenters
 * @property string $category
 */
class ProductPresenter extends BasePresenter {
    /** @var ProductsFacade $productsFacade */
    private $productsFacade;
    /** @var ProductCartFormFactory $productCartFormFactory */
    private $productCartFormFactory;
    /** @var ProductsFilterFormFactory $productsFilterFormFactory */
    private $productsFilterFormFactory;

    /** @persistent */
    public $filter = [];


    /**
     * Akce pro zobrazení jednoho produktu
     * @param string $url
     * @throws BadRequestException
     */
    public function renderShow(string $url): void {
        try {
            $product = $this->productsFacade->getProductByUrl($url);
        } catch (\Exception $e) {
            throw new BadRequestException('Produkt nebyl nalezen.');
        }

        $this->template->product = $product;
    }

    /**
     * Akce pro vykreslení přehledu produktů
     */
    public function renderList(): void {
        $filter = $this->filter;
        $whereArr = [];
        if (isset($filter['orderBy'])) {
            $whereArr['order'] = str_replace('-', ' ', $filter['orderBy']);
        } else {
            $whereArr['order'] = 'name';
        }
        if (!empty($filter['categories'])) {
            $whereArr['categories'] = $filter['categories'];
        }
        if (isset($filter['onlyAvailable']) && $filter['onlyAvailable'] == '1') {
            $whereArr['onlyAvailable'] = true;
        }
        $this->template->products = $this->productsFacade->findProducts($whereArr);
    }

    protected function createComponentProductsFilterForm(): ProductsFilterForm {
        $form = $this->productsFilterFormFactory->create();
        $form->createSubcomponents(!empty($this->filter));

        if (!empty($this->filter)) {
            $form->setDefaults($this->filter);
        }

        $form->onReset[] = function() {
            $this->filter = [];
            $this->redirect('this');
        };

        $form->onFilter[] = function() use ($form) {
            $this->filter = [...$form->getValues()];
            $this->redirect('this');
        };

        return $form;
    }

    protected function createComponentProductCartForm(): Multiplier {
        return new Multiplier(function($productId) {
            $form = $this->productCartFormFactory->create();
            $form->setDefaults(['productId'=>$productId]);
            $form->onSubmit[] = function(ProductCartForm $form) {
                try {
                    $product = $this->productsFacade->getProduct($form->values->productId);
                    //kontrola zakoupitelnosti
                } catch (\Exception $e) {
                    $this->flashMessage('Produkt nejde přidat do košíku','error');
                    if ($this->isAjax()) {
                        $this->redrawControl('flashes');
                    } else {
                        $this->redirect('this');
                    }
                }
                //přidání do košíku
                /** @var CartControl $cart */
                $cart = $this->getComponent('cart');
                $cart->addToCart($product, (int)$form->values->count);

                $this->flashMessage('Produkt přidán do košíku: ' . $product->name);
                if ($this->isAjax()) {
                    $this->redrawControl('flashes');
                    $this->redrawControl('cart');
                } else {
                    $this->redirect('this');
                }
            };

            return $form;
        });
    }

    #region injections
    public function injectProductsFacade(ProductsFacade $productsFacade): void {
        $this->productsFacade = $productsFacade;
    }

    public function injectProductCartFormFactory(ProductCartFormFactory $productCartFormFactory): void {
        $this->productCartFormFactory = $productCartFormFactory;
    }

    public function injectProductsFilterFormFactory(ProductsFilterFormFactory $productsFilterFormFactory): void {
        $this->productsFilterFormFactory = $productsFilterFormFactory;
    }
    #endregion injections
}