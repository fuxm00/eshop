<?php

namespace App\FrontModule\Components\ProductCartForm;

use App\FrontModule\Components\CartControl\CartControl;
use Nette;
use Nette\Application\UI\Form;
use Nette\SmartObject;

/**
 * Class ProductCartForm
 * @package App\FrontModule\Components\ProductCartForm
 */
class ProductCartForm extends Form {

  use SmartObject;

  private CartControl $cartControl;

  /**
   * ProductCartForm constructor.
   * @param Nette\ComponentModel\IContainer|null $parent
   * @param string|null $name
   */
  public function __construct(Nette\ComponentModel\IContainer $parent = null, string $name = null){
    parent::__construct($parent, $name);

    $renderer = $this->getRenderer();

    $renderer->wrappers['controls']['container'] = null;
    $renderer->wrappers['pair']['container'] = null;
    $renderer->wrappers['label']['container'] = null;
    $renderer->wrappers['control']['container'] = null;

    $this->createSubcomponents();
  }

  /**
   * Metoda pro předání komponenty košíku jako závislosti
   * @param CartControl $cartControl
   */
  public function setCartControl(CartControl $cartControl):void {
    $this->cartControl=$cartControl;
  }

  private function createSubcomponents(){
      $this->setHtmlAttribute('class', 'cart-form');

      $this->addHidden('productId');
      $this->addInteger('count')
          ->addRule(Form::RANGE,'Chybný počet kusů.',[1,100])
          ->setDefaultValue(1)
          ->setRequired('Počet kusů je povinný.')
          ->setHtmlAttribute('class', 'count-input');

      $this->addSubmit('ok','Do košíku')
          ->setHtmlAttribute('class', 'count-submit-btn')
          ->setHtmlAttribute('class', 'green-btn');

  }

}