<?php

namespace App\Model\Facades;

use App\Model\Entities\Product;
use App\Model\Repositories\ProductRepository;
use Nette\Http\FileUpload;
use Nette\Utils\Strings;

class ProductsFacade {
    private ProductRepository $productsRepository;

    public function __construct(ProductRepository $productsRepository){
        $this->productsRepository=$productsRepository;
    }

    public function getProduct(int $id): Product {
        return $this->productsRepository->find($id);
    }

    public function findProducts(array $params = null, int $offset = null, int $limit = null): array {
        return $this->productsRepository->findAllBy($params,$offset,$limit);
    }

    public function saveProduct(Product &$product): void {
        #region URL produktu
        if (empty($product->url)) {
            //pokud je URL prázdná, vygenerujeme ji podle názvu produktu
            $baseUrl = Strings::webalize($product->name);
        } else {
            $baseUrl = $product->url;
        }

        #region vyhledání produktů se shodnou URL (v případě shody připojujeme na konec URL číslo)
        $urlNumber = 1;
        $url = $baseUrl;
        $productId = isset($product->productId) ? $product->productId : null;
        try {
            while ($existingProduct = $this->getProductByUrl($url)) {
                if ($existingProduct->productId == $productId){
                    //ID produktu se shoduje => je v pořádku, že je URL stejná
                    $product->url = $url;
                    break;
                }
                $urlNumber++;
                $url = $baseUrl.$urlNumber;
            }
        } catch (\Exception $e) {
            //produkt nebyl nalezen => URL je použitelná
        }
        $product->url = $url;
        #endregion vyhledání produktů se shodnou URL (v případě shody připojujeme na konec URL číslo)
        #endregion URL produktu

        $this->productsRepository->persist($product);
    }

    public function saveProductPhoto(FileUpload $fileUpload, Product &$product): void {
        if ($fileUpload->isOk() && $fileUpload->isImage()){
            $fileExtension = strtolower($fileUpload->getImageFileExtension());
            $fileUpload->move(__DIR__.'/../../../www/img/products/'.$product->productId.'.'.$fileExtension);
            $product->photoExtension = $fileExtension;
            $this->saveProduct($product);
        }
    }

    //Volat, pokud jsou potřeba hromadně smazat obrázky nepoužívaných produktů
    public function deleteUnusedProductImages(): void {
        $products = $this->findProducts();
        $productImages = glob(__DIR__.'/../../../www/img/products/*');
        foreach ($productImages as $productImage) {
            $productImageArr = explode('.', basename($productImage));
            $productId = $productImageArr[0];
            $imageExtension = $productImageArr[1];
            $productFound = false;
            foreach ($products as $product) {
                if ($product->productId == $productId && $product->photoExtension == $imageExtension) {
                    $productFound = true;
                    break;
                }
            }
            if (!$productFound) {
                unlink(__DIR__.'/../../../www/img/products/'.$productId.'.'.$imageExtension);
            }
        }
    }

    public function deleteProduct(Product $product): bool {
        try {
            $productImage = __DIR__.'/../../../www/img/products/'.$product->productId.'.'.$product->photoExtension;
            $result = (bool)$this->productsRepository->delete($product);
            if ($result) {
                $this->deleteProductImage($productImage);
            }
            return $result;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function deleteProductImage(string $productImage): void {
        if (file_exists($productImage)) {
            unlink($productImage);
        }
    }

    public function getProductByUrl(string $url):Product {
        return $this->productsRepository->findBy(['url'=>$url]);
    }

}