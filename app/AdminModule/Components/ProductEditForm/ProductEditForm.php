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
            ->setRequired('Musíte zadat název produktu')
            ->setMaxLength(100);
        $this->addText('url','URL produktu')
            ->setMaxLength(100)
            ->addFilter(function(string $url){
                return Nette\Utils\Strings::webalize($url);
            })
            ->addRule(function(Nette\Forms\Controls\TextInput $input) use ($productId){
                try {
                    $existingProduct = $this->productsFacade->getProductByUrl($input->value);
                    return $existingProduct->productId==$productId->value;
                } catch (\Exception $e) {
                    return true;
                }
            },'Zvolená URL je již obsazena jiným produktem');
        $this->addText('price','Cena')
            ->setRequired('Musíte zadat cenu produktu')
            ->addRule(Nette\Forms\Form::FLOAT,'Cena musí být číslo');
        $this->addTextArea('description','Popis produktu')
            ->setRequired(false);
        $this->addCheckbox('available', 'Nabízeno ke koupi')
            ->setDefaultValue(true);

        $allCategories = [];
        foreach ($this->categoriesFacade->findCategories() as $category) {
            $allCategories[$category->categoryId] = $category->title;
        }
        if (!empty($allCategories)) {
            $this->addCheckboxList('categories','Kategorie', $allCategories);
        }

        #region obrázek
        $photoUpload = $this->addUpload('photo','Fotka produktu');
        //pokud není zadané ID produktu, je nahrání fotky povinné
        $photoUpload //vyžadování nahrání souboru, pokud není známé productId
        ->addConditionOn($productId, Nette\Forms\Form::EQUAL, '')
            ->setRequired('Pro uložení nového produktu je nutné nahrát jeho fotku.');

        $photoUpload //limit pro velikost nahrávaného souboru
        ->addRule(Nette\Forms\Form::MAX_FILE_SIZE, 'Nahraný soubor je příliš velký', 1000000);

        $photoUpload //kontrola typu nahraného souboru, pokud je nahraný
        ->addCondition(Nette\Forms\Form::FILLED)
            ->addRule(function(Nette\Forms\Controls\UploadControl $photoUpload) {
                $uploadedFile = $photoUpload->value;
                if ($uploadedFile instanceof Nette\Http\FileUpload){
                    $extension = strtolower($uploadedFile->getImageFileExtension());
                    return in_array($extension, ['jpg','jpeg','png']);
                }
                return false;
            },'Je nutné nahrát obrázek ve formátu JPEG či PNG.');
        #endregion obrázek

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
            $product->assign($values, ['name', 'url', 'description', 'price', 'available']);
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

            //uložení fotky
            if (($values['photo'] instanceof Nette\Http\FileUpload) && ($values['photo']->isOk())){
                try {
                    $this->productsFacade->saveProductPhoto($values['photo'], $product);
                } catch (\Exception $e) {
                    $this->onFailed('Produkt byl uložen, ale nepodařilo se uložit jeho fotku.');
                }
            }

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
                'url'=>$data->url,
                'price'=>$data->price,
                'description'=>$data->description,
                'categories'=>$categories
            ];
        }
        parent::setDefaults($data, $erase);
        return $this;
    }

}