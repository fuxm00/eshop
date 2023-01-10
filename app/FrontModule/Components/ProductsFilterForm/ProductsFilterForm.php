<?php

namespace App\FrontModule\Components\ProductsFilterForm;

use App\Model\Facades\CategoriesFacade;
use Nette;
use Nette\Application\UI\Form;
use Nette\SmartObject;
use Nextras\FormsRendering\Renderers\Bs4FormRenderer;
use Nextras\FormsRendering\Renderers\FormLayout;

class ProductsFilterForm extends Form {

  use SmartObject;

  private CategoriesFacade $categoriesFacade;

  /** @var callable[] $onFilter */
  public array $onFilter = [];
  /** @var callable[] $onReset */
  public array $onReset = [];

  public function __construct(Nette\ComponentModel\IContainer $parent = null, string $name = null, CategoriesFacade $categoriesFacade) {
    parent::__construct($parent, $name);
    $this->categoriesFacade = $categoriesFacade;
    $this->setRenderer(new Bs4FormRenderer(FormLayout::VERTICAL));
  }

  public function createSubcomponents($showRemoveButton = true) {
    $this->setHtmlAttribute('class', 'filter-form');

    if ($showRemoveButton) {
        $this->addSubmit('removeFilter', 'Zrušit filtr')
            ->setHtmlAttribute('class', 'btn btn-danger btn-sm products-filter--remove')
            ->onClick[] = function() {
            $this->onReset();
        };
    }

    $allCategories = [];
    foreach ($this->categoriesFacade->findCategories() as $category) {
        $allCategories[$category->categoryId] = $category->title;
    }
    if (!empty($allCategories)) {
        $this->addCheckboxList('categories','Kategorie', $allCategories);
    }

    $this->addSelect('orderBy', 'Seřadit podle', [
        'name' => 'Názvu',
        'price-desc' => 'Ceny: od nejvyšší',
        'price-asc' => 'Ceny: od nejnižší',
    ]);

    $this->addCheckbox('onlyAvailable', 'Zobrazit pouze dostupné produkty');

    $this->addSubmit('ok', 'Filtrovat')
        ->setHtmlAttribute('class', 'filter-submit-btn')
        ->setHtmlAttribute('class', 'green-btn')
        ->onClick[] = function() {
          $this->onFilter($this->getValues('array'));
        };
  }
}