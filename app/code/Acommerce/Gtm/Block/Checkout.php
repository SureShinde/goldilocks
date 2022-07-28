<?php
namespace Acommerce\Gtm\Block;

use Acommerce\Gtm\Helper\Data as Helper;
/**
 * Class \Acommerce\Gtm\Block\Checkout
 */
class Checkout extends \Acommerce\Gtm\Block\Core
{
    /**
     * Returns the product details for the purchase gtm event
     * @return array
     */
    public function getProducts() {
        $quote = $this->getQuote();
        $products = [];

        foreach ($quote->getAllItems() as $item) {

            if($item->getProductType() != 'configurable') {
                $product = $item->getProduct();
                $sku = $item->getSku();
                $productName = $item->getName();

                if($item->getParentItemId()) {
                    $item = $item->getParentItem();
                }

                $productDetail = [];
                // $productDetail['name'] = html_entity_decode($item->getName());
                // $productDetail['id'] = $productName;//$this->helper->getGtmProductId($product);
                $productDetail['sku'] = $sku;//$this->helper->getGtmRootProductId($product);
                $productDetail['variant'] = $this->helper->getProductVariant($product);
                $productDetail['price'] = Helper::numberFormat($item->getPriceInclTax(), 2);
				
				if($productDetail['price'] == "0.00"){
					$productDetail['name'] = "FREE - ".html_entity_decode($item->getName());
					$productDetail['id']   = "FREE - ".$productName;//$this->helper->getGtmProductId($product);
				}else{
					$productDetail['name'] = html_entity_decode($item->getName());
					$productDetail['id'] = $productName;//$this->helper->getGtmProductId($product);
				}

                if ($this->helper->isBrandEnabled()) :
                    $productDetail['brand'] = $this->helper->getGtmBrand($product);
                endif;

                $productDetail['category'] = $this->helper->getGtmCategoryByProduct($product);
                $productDetail['quantity'] = $item->getQty();
                $products[] = $productDetail;
            }

        }

        return $products;
    }
}