<?php

namespace App\FrontModule\Presenters;

use App\Model\Facades\ProductsFacade;

class HomepagePresenter extends BasePresenter {

    /** @var ProductsFacade $productsFacade */
    private ProductsFacade $productsFacade;

    public function renderSitemap(): void {
        $this->template->products = $this->productsFacade->findProducts(['order'=>'name']);
    }

    #region injections
    public function injectProductsFacade(ProductsFacade $productsFacade): void {
        $this->productsFacade=$productsFacade;
    }
    #endregion injections

}
