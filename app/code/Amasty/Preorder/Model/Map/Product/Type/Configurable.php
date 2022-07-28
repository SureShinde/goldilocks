<?php

namespace Amasty\Preorder\Model\Map\Product\Type;

use Amasty\Preorder\Model\Product\GetPreorderInformation;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Model\StockRegistry;

class Configurable
{
    /**
     * @var StockRegistry
     */
    private $stockRegistry;

    /**
     * @var ProductInterface
     */
    private $product;

    /**
     * @var GetPreorderInformation
     */
    private $getPreorderInformation;

    public function __construct(StockRegistry $stockRegistry, GetPreorderInformation $getPreorderInformation)
    {
        $this->stockRegistry = $stockRegistry;
        $this->getPreorderInformation = $getPreorderInformation;
    }

    /**
     * @return ProductInterface
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param ProductInterface $product
     *
     * @return $this;
     */
    public function setProduct($product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return array
     */
    public function getProductPreorderMap()
    {
        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $typeInstance */
        $typeInstance       = $this->getProduct()->getTypeInstance();
        $elementaryProducts = $typeInstance->getUsedProducts($this->getProduct());
        $allowedAttributes  = $typeInstance->getConfigurableAttributes($this->getProduct());

        $map = [];
        $elementaryProducts[] = $this->getProduct();
        foreach ($elementaryProducts as $product) {
            $preorderInformation = $this->getPreorderInformation->execute($product);
            if ($preorderInformation->isPreorder()) {
                if (!$this->getStockStatusMsi($product->getId())) {
                    continue;
                }

                $map[$product->getId()] = [
                    'cartLabel' => $preorderInformation->getCartLabel(),
                    'note' => $preorderInformation->getNote(),
                    'attributes' => []
                ];

                foreach ($allowedAttributes as $attribute) {
                    $productAttribute = $attribute->getProductAttribute();
                    $productAttributeId = $productAttribute->getId();
                    $attributeValue = $product->getData($productAttribute->getAttributeCode());

                    $map[$product->getId()]['attributes'][$productAttributeId] = $attributeValue;
                }
            }
        }

        return $map;
    }

    /**
     * @return array
     */
    public function getConfigurableAttributes()
    {
        $attributes = [];
        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $typeInstance */
        $typeInstance      = $this->getProduct()->getTypeInstance();
        $allowedAttributes = $typeInstance->getConfigurableAttributes($this->getProduct());
        foreach ($allowedAttributes as $attribute) {
            $attributes[$attribute->getProductAttribute()->getId()] = $attribute->getLabel();
        }

        return $attributes;
    }

    public function getIsAllProductsPreorder(): bool
    {
        $elementaryProducts = $this->getProduct()->getTypeInstance()->getUsedProducts($this->getProduct());
        $isAllProductsPreorder = true;
        foreach ($elementaryProducts as $product) {
            if (!$this->getPreorderInformation->execute($product)->isPreorder()) {
                $isAllProductsPreorder = false;
                break;
            }
        }

        return $isAllProductsPreorder;
    }

    private function getStockStatusMsi(int $productId): int
    {
        return (int) $this->stockRegistry->getStockStatus($productId)->getStockStatus();
    }
}
