<?php

namespace Magenest\GoogleTagManager\Block;

interface EnhancedEcommerceInterface
{
    /**
     * Retrieve list of products
     *
     * @return array
     */
    public function getProducts();

    /**
     * Retrieve list of products or product container items
     *
     * @return array
     */
    public function getItems();

    /**
     * Retrieve product details for analytics
     *
     * @param \Magento\Framework\DataObject $object
     * @return array
     */
    public function getProductDetails($object);

    /**
     * Retrieve basic item attributes for analytics
     *
     * @param \Magento\Framework\DataObject $object
     * @return array
     */
    public function getBasicItemAttributes($object);

    /**
     * Retrieve product attributes for analytics
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getProductAttributes($product);

    /**
     * Retrieve custom product attributes or non-attribute data for analytics
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getCustomAttributes($product);
}
