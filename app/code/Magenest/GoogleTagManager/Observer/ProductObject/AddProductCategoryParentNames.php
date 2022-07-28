<?php

namespace Magenest\GoogleTagManager\Observer\ProductObject;

use Magento\Framework\DataObject;
use Magenest\GoogleTagManager\Helper\Data;

class AddProductCategoryParentNames implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var Data
     */
    private $data;

    /**
     * @var \Magenest\GoogleTagManager\Model\Catalog\Category\NameResolver
     */
    private $nameResolver;

    /**
     * @var \Magenest\GoogleTagManager\Model\Catalog\Category\CurrentCategoryResolver
     */
    private $categoryResolver;

    public function __construct(
        Data $data,
        \Magenest\GoogleTagManager\Model\Catalog\Category\NameResolver $nameResolver,
        \Magenest\GoogleTagManager\Model\Catalog\Category\CurrentCategoryResolver $categoryResolver
    ) {
        $this->data = $data;
        $this->nameResolver = $nameResolver;
        $this->categoryResolver = $categoryResolver;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->data->reportParentCategories()) {
            return;
        }

        /** @var DataObject $data */
        $data = $observer->getData('data');

        $category = $this->categoryResolver->getCurrentCategory();

        if ($category) {
            $categoryName = $this->nameResolver->resolve($category);
            $data->setData('category', $categoryName);
        }
    }
}
