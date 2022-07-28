<?php

namespace Magenest\GoogleTagManager\Block;

use Magenest\GoogleTagManager\Api\ProductObjectGeneratorInterface;
use Magenest\GoogleTagManager\Helper\Data as GtmDataHelper;

/**
 * Extendable class for any list of products
 */
abstract class ProductList extends AbstractGtmBlock
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection
     */
    private $productCollection;

    /**
     * @var \Magenest\GoogleTagManager\Helper\Data
     */
    private $gtmHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magenest\GoogleTagManager\Helper\AttributeCodes $attributeCodes,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        GtmDataHelper $gtmHelper,
        ProductObjectGeneratorInterface $productObjectGenerator,
        array $data = []
    ) {
        parent::__construct($context, $attributeCodes, $jsonHelper, $productObjectGenerator, $data);
        $this->gtmHelper = $gtmHelper;
    }

    public function getProductDetails($object)
    {
        $data['list'] = $this->getListName();
        $data = parent::getProductDetails($object);

        return $data;
    }

    abstract public function getListName();

    public function getItems()
    {
        return $this->getProductCollection()->getItems();
    }

    /**
     * Returns an instance of an assigned block via a layout update file
     *
     * @return bool|\Magento\Framework\View\Element\Template
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getListBlock()
    {
        return $this->getLayout()->getBlock($this->getBlockName());
    }

    /**
     * @inheritDoc
     *
     * Overridden since getItems has been changed to return raw array data instead of array of models
     */
    public function getProducts()
    {
        $products = \array_values($this->getProductCollection()->getItems());
        $products = \array_map([$this, 'getProductDetails'], $products);

        return $this->addProductPositions($products);
    }

    /**
     * Retrieve how many products should be displayed on page
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPageSize()
    {
        return $this->getProductCollection()->getPageSize();
    }

    /**
     * Get current collection page
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCurPage()
    {
        return $this->getProductCollection()->getCurPage();
    }

    /**
     * Adds 'position' fields to products
     *
     * @param array $products
     *
     * @return string[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addProductPositions($products)
    {
        $position = ($this->getCurPage() - 1) * $this->getPageSize() + 1;

        foreach ($products as $key => $product) {
            $products[$key]['position'] = $position++;
        }

        return $products;
    }

    /**
     * Rewrite to disable content if needed.
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->gtmHelper->isEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Retrieve loaded category collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductCollection()
    {
        if ($this->productCollection === null) {
            $this->productCollection = $this->getListBlock()->getLoadedProductCollection();
        }

        return $this->productCollection;
    }
}
