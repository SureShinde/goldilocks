<?php
namespace Magenest\GoogleTagManager\Plugin\Catalog\Block\Product\AbstractProduct;

use Magenest\GoogleTagManager\Helper\Data;
use Magenest\GoogleTagManager\Model\Catalog\Category\CurrentCategoryResolver;
use Magenest\GoogleTagManager\Model\Catalog\Category\NameResolver;
use Magenest\GoogleTagManager\Model\Quote\Item\PurchaseCategory;

class AddPurchaseCategoryToCartUrl
{
    /**
     * @var Data
     */
    private $dataHelper;
    /**
     * @var NameResolver
     */
    private $nameResolver;
    /**
     * @var CurrentCategoryResolver
     */
    private $categoryResolver;

    public function __construct(
        Data $dataHelper,
        NameResolver $nameResolver,
        CurrentCategoryResolver $categoryResolver
    ) {
        $this->dataHelper = $dataHelper;
        $this->nameResolver = $nameResolver;
        $this->categoryResolver = $categoryResolver;
    }

    // phpcs:ignore VCQP.CodeAnalysis.UnusedFunctionParameter.Found
    public function beforeGetAddToCartUrl($subject, $product, $additional = [])
    {
        if (!$this->dataHelper->isEnabled() || !$this->dataHelper->reportQuoteItemCategory()) {
            return;
        }

        $category = $this->categoryResolver->getCurrentCategory();
        $categoryName = $category->getName();
        if ($this->dataHelper->reportParentCategories()) {
            $categoryName = $this->nameResolver->resolve($category);
        }

        /**
         * Purchase category field is added as a query parameter, since encoded
         * slashes are generally not allowed by apache and would require special
         * configuration in apache to function properly.
         *
         * Putting the parameter in the `_query` section forces magento to
         * output the parameter in the query string (?key=value), rather than
         * including it in the URL path (e.g. path/key/value).
         *
         * @see https://stackoverflow.com/questions/3235219/urlencoded-forward-slash-is-breaking-url
         */
        $additional['_query'][PurchaseCategory::PURCHASE_CATEGORY] = $categoryName;

        return [$product, $additional];
    }
}
