<?php

namespace Magenest\GoogleTagManager\Helper;

use Magento\Quote\Model\Quote\Item;
use Magenest\GoogleTagManager\Api\ProductObjectGeneratorInterface;

class CatalogSession
{
    const REMOVE_ITEM_KEY = 'removed_item_data';
    const ADD_ITEM_KEY    = 'added_item_data';

    /**
     * @var \Magento\Catalog\Model\Session
     */
    private $catalogSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magenest\GoogleTagManager\Helper\AttributeCodes
     */
    private $attributeCodes;
    /**
     * @var ProductObjectGeneratorInterface
     */
    private $productObjectGenerator;

    /**
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magenest\GoogleTagManager\Helper\AttributeCodes $attributeCodes
     * @param ProductObjectGeneratorInterface|null $productObjectGenerator
     */
    public function __construct(
        \Magento\Catalog\Model\Session $catalogSession, // phpcs:ignore MEQP2.Classes.MutableObjects.MutableObjects
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magenest\GoogleTagManager\Helper\AttributeCodes $attributeCodes,
        ProductObjectGeneratorInterface $productObjectGenerator
    ) {
        $this->catalogSession = $catalogSession;
        $this->storeManager = $storeManager;
        $this->attributeCodes = $attributeCodes;
        $this->productObjectGenerator = $productObjectGenerator;
    }

    /**
     * Get stored event data
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @param bool $clear
     *
     * @return array|null
     */
    protected function getEventData($clear = false) // phpcs:ignore VCQP.PHP.ProtectedClassMember.FoundProtected
    {
        return $this->catalogSession->getData('product_event_data', $clear);
    }

    /**
     * Store event data for later usage
     *
     * @param array $data
     */
    protected function setEventData($data) // phpcs:ignore VCQP.PHP.ProtectedClassMember.FoundProtected
    {
        $this->catalogSession->setData('product_event_data', $data);
    }

    /**
     * Append event data to saved event data
     *
     * @param array $data
     * @param string $key REMOVE_ITEM_KEY|ADD_ITEM_KEY
     */
    protected function appendEventData($data, $key) // phpcs:ignore VCQP.PHP.ProtectedClassMember.FoundProtected
    {
        $events = $this->getEventData(false) ?: [];

        $events[$key][] = $data;

        $this->setEventData($events);
    }

    /**
     * @param Item $item
     * @param int $qty
     */
    public function addItem($item, int $qty)
    {
        /**
         * Note: For configurable products, the product passed here will reflect properties of the
         * simple product (e.g. for SKU) due to the product being configured.
         * @see \Magento\Catalog\Model\Product\Type\AbstractType::getSku
         */
        $data = $this->prepareProductData($item->getProduct(), null, $qty);
        $data = $this->prepareQuoteItemData($item, $data);

        $this->appendEventData($data, self::ADD_ITEM_KEY);
    }

    /**
     * @param Item $item
     * @param array $data
     * @return array
     */
    private function prepareQuoteItemData($item, $data = [])
    {
        return $this->productObjectGenerator->generate($item, $data);
    }

    /**
     * @param Item $item
     * @param int $qty
     */
    public function removeItem($item, int $qty)
    {
        $data = $this->prepareProductData($item->getProduct(), null, $qty);
        $data = $this->prepareQuoteItemData($item, $data);

        $this->appendEventData($data, self::REMOVE_ITEM_KEY);
    }

    /**
     * Prepare product data for GTM event
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string|null $parentName
     * @param int $qty
     * @return array
     */
    public function prepareProductData($product, $parentName, $qty)
    {
        $name = ($parentName) ? $parentName : $product->getName();

        $productData = [
            'name' => $name,
            'id' => $product->getSku(),
            'price' => $product->getPrice(),
            'quantity' => $qty,
            /**
             * @deprecated
             *
             * According to https://developers.google.com/tag-manager/enhanced-ecommerce#product-clicks
             * this is incorrect field name, should be 'quantity'. This field has been left
             * behind for backwards compatibility for projects that might rely on this field in one
             * way or another.
             */
            'qty' => $qty,
        ];

        $productData = \array_merge(
            $productData,
            $this->attributeCodes->getProductAttributes($product),
            $this->attributeCodes->getCustomAttributes($product)
        );

        return $this->productObjectGenerator->generate($product, $productData);
    }

    /**
     * Retrieve catalog session
     *
     * @return \Magento\Catalog\Model\Session
     */
    public function getCatalogSession()
    {
        return $this->catalogSession;
    }

    /**
     * Retrieve product data for GTM event
     *
     * @return array|null
     */
    public function getProductData()
    {
        $productData = $this->getEventData(true);

        if ($productData) {
            $productData = [
                'currencyCode' => $this->storeManager->getStore()->getCurrentCurrencyCode(),
                'products' => $productData,
            ];
        }

        return $productData;
    }
}
