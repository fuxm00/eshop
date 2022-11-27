<?php

namespace App\AdminModule\Components\ProductEditForm;

use App\Model\Entities\Product;
use App\Model\Facades\CategoriesFacade;
use App\Model\Facades\ProductsFacade;
use Nette;
use Nette\Application\UI\Form;
use Nette\SmartObject;
use Nextras\FormsRendering\Renderers\Bs4FormRenderer;
use Nextras\FormsRendering\Renderers\FormLayout;

/**
 * Class ProductEditForm
 * @package App\AdminModule\Components\ProductEditForm
 *
 * @method onFinished(string $message = '')
 * @method onFailed(string $message = '')
 * @method onCancel()
 */
class ProductEditForm extends Form {

    use SmartObject;

    /** @var callable[] $onFinished */
    public array $onFinished = [];
    /** @var callable[] $onFailed */
    public array $onFailed = [];
    /** @var callable[] $onCancel */
    public array $onCancel = [];

    private ProductsFacade $productsFacade;

    private CategoriesFacade $categoriesFacade;

    /**
     * ProductEditForm constructor.
     * @param Nette\ComponentModel\IContainer|null $parent
     * @param string|null $name
     * @param ProductsFacade $productsFacade
     * @param CategoriesFacade $categoriesFacade
     * @noinspection PhpOptionalBeforeRequiredParametersInspection
     */
    public function __construct(Nette\ComponentModel\IContainer $parent = null, string $name = null, ProductsFacade $productsFacade, CategoriesFacade $categoriesFacade) {
        parent::__construct($parent, $name);
        $this->setRenderer(new Bs4FormRenderer(FormLayout::VERTICAL));
        $this->productsFacade = $productsFacade;
        $this->categoriesFacade = $categoriesFacade;
        $this->createSubcomponents();
    }

    private function createSubcomponents(): void {
        $productId = $this->addHidden('productId');
        $this->addText('name','Název produktu')
            ->setRequired('Musíte zadat název produktu');
        $this->addText('price','Cena')
            ->setRequired('Musíte zadat cenu produktu')
            ->addRule(Nette\Forms\Form::FLOAT,'Cena musí být číslo');
        $this->addTextArea('description','Popis produktu')
            ->setRequired(false);
        $allCategories = [];
        foreach ($this->categoriesFacade->findCategories() as $category) {
            $allCategories[$category->categoryId] = $category->title;
        }
        if (!empty($allCategories)) {
            $this->addCheckboxList('categories','Kategorie', $allCategories);
        }
        $this->addSubmit('ok','uložit')
            ->onClick[] = function() {
            $values = $this->getValues('array');
            if (!empty($values['productId'])) {
                try {
                    $product = $this->productsFacade->getProduct($values['productId']);
                } catch (\Exception $e) {
                    $this->onFailed('Požadovaný produkt nebyl nalezen.');
                    return;
                }
            } else {
                $product = new Product();
            }
            $product->assign($values, ['name', 'description', 'price']);
            $this->productsFacade->saveProduct($product);

            $product->removeAllCategories();
            if (!empty($values['categories'])) {
                foreach ($values['categories'] as $categoryId) {
                    try {
                        $category = $this->categoriesFacade->getCategory($categoryId);
                        $product->addToCategories($category);
                    } catch (\Exception $e) {
                        $this->onFailed('Požadovaná kategorie nebyla nalezena.');
                    }
                }
            }
            $this->productsFacade->saveProduct($product);

            $this->setValues(['productId'=>$product->productId]);
            $this->onFinished('Produkt byl uložen.');
        };
        $this->addSubmit('storno','Zrušit')
            ->setValidationScope([$productId])
            ->onClick[] = function(){
            $this->onCancel();
        };
    }

    /**
     * Metoda pro nastavení výchozích hodnot formuláře
     * @param Product|array|object $data
     * @param bool $erase = false
     * @return $this
     */
    public function setDefaults($data, bool $erase = false): self {
        if ($data instanceof Product) {
            $categories = array_map(function($category) {
                return $category->categoryId;
            }, $data->categories);
            $data = [
                'productId'=>$data->productId,
                'name'=>$data->name,
                'price'=>$data->price,
                'description'=>$data->description,
                'categories'=>$categories
            ];
        }
        parent::setDefaults($data, $erase);
        return $this;
    }

}