<?php

namespace Magenest\Bundle\ViewModel;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Pricing\Render;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class BundleViewModel implements ArgumentInterface
{
    protected Repository $repository;
    protected Image $imageHlp;

    /**
     * Data constructor.
     * @param Repository $repository
     * @param Image $imageHlp
     */
    public function __construct(
        Repository $repository,
        Image      $imageHlp
    )
    {
        $this->repository = $repository;
        $this->imageHlp = $imageHlp;
    }

    /**
     * @param Product $productObj
     * @return string
     */
    public function getImageUrlByProduct(Product $productObj): string
    {
        $image = $productObj->getData('small_image') ?: $productObj->getData('thumbnail');
        $productImage = $this->imageHlp
            ->init($productObj, 'product_banner_image')
            ->setImageFile($image);
        if ($image) {
            $imageUrl = $productImage->getUrl();
        } else {
            $imageUrl = $this->repository->getUrl($this->imageHlp->getPlaceholder('small_image'));
        }

        return $imageUrl;
    }

    /**
     * @param Product $productObj
     * @param $block
     * @return string
     */
    public function getPriceHtml(Product $productObj, $block)
    {
        $priceRender = $this->getPriceRender($block);

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                FinalPrice::PRICE_CODE,
                $productObj,
                [
                    'include_container' => true,
                    'display_minimal_price' => true,
                    'zone' => Render::ZONE_ITEM_LIST,
                    'list_category_page' => true
                ]
            );
        }

        return $price;
    }

    protected function getPriceRender($block)
    {
        return $block->getLayout()->getBlock('product.price.render.default')
            ->setData('is_product_list', true);
    }
}
