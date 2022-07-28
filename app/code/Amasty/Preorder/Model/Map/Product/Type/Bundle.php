<?php

namespace Amasty\Preorder\Model\Map\Product\Type;

use Amasty\Preorder\Model\Product\ExtensionAttributeRegistry;
use Amasty\Preorder\Model\Product\Processor;
use Magento\Catalog\Api\Data\ProductInterface;

class Bundle
{
    /**
     * @var array
     */
    private $bundleOptionsData;

    /**
     * @var array
     */
    private $bundleSelectionsData;

    /**
     * @var bool
     */
    private $isAllProductsPreorder;

    /**
     * @var ProductInterface
     */
    private $product;

    /**
     * @var ExtensionAttributeRegistry
     */
    private $attributeRegistry;

    /**
     * @var Processor
     */
    private $processor;

    public function __construct(ExtensionAttributeRegistry $attributeRegistry, Processor $processor)
    {
        $this->attributeRegistry = $attributeRegistry;
        $this->processor = $processor;
    }

    /**
     * @return array
     */
    public function getBundleSelectionsData()
    {
        $this->prepareBundleData();

        return $this->bundleSelectionsData;
    }

    /**
     * @return array
     */
    public function getBundleOptionsData()
    {
        $this->prepareBundleData();

        return $this->bundleOptionsData;
    }

    public function getIsAllProductsPreorder(): bool
    {
        $this->prepareBundleData();

        return $this->isAllProductsPreorder;
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
     * @return $this
     */
    private function prepareBundleOptions()
    {
        if ($this->bundleOptionsData === null) {
            $this->bundleOptionsData = [];
            /** @var \Magento\Bundle\Model\Product\Type $typeInstance */
            $typeInstance = $this->getProduct()->getTypeInstance();

            $options = $typeInstance->getOptions($this->getProduct());
            foreach ($options as $option) {
                /** @var $option \Magento\Bundle\Model\Option */
                $this->bundleOptionsData[$option->getId()] = [
                    'isSingle' => null,
                    'isMultiSelection' => (bool)$option->isMultiSelection(),
                    'isRequired' => (bool)$option->getRequired(),
                    'selectionCount' => 0, // for a while
                    'isPreorder' => null,
                    'message' => null,
                    'selectionId' => 0,
                ];
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function prepareBundleData()
    {
        if ($this->bundleSelectionsData !== null) {
            return $this;
        }

        $this->bundleSelectionsData = [];

        $this->prepareBundleOptions();

        /** @var \Magento\Bundle\Model\Product\Type $typeInstance */
        $typeInstance = $this->getProduct()->getTypeInstance();
        $optionIds = $typeInstance->getOptionsIds($this->getProduct());
        /** @var \Magento\Bundle\Model\Selection $selections */
        $selections = $typeInstance->getSelectionsCollection($optionIds, $this->getProduct())->getItems();
        $productIds = [];
        foreach ($selections as $selection) {
            $productIds[] = $selection->getProductId();
        }

        $selectionProductCollection = $this->getProduct()->getCollection()->addFieldToFilter('entity_id', $productIds);
        $this->isAllProductsPreorder = true;

        $this->processor->execute($selectionProductCollection->getItems());
        foreach ($selections as $selection) {
            /** @var \Magento\Catalog\Model\Product $product */
            /** @var \Magento\Bundle\Model\Selection $selection */
            $product = $selectionProductCollection->getItemById($selection->getProductId());
            if ($product === null) {
                continue;
            }

            $isPreorder = $product->getExtensionAttributes()->getPreorderInfo()->isPreorder();
            if (!$isPreorder) {
                $this->isAllProductsPreorder = false;
            }

            $note = $product->getExtensionAttributes()->getPreorderInfo()->getNote();
            $cartLabel = $product->getExtensionAttributes()->getPreorderInfo()->getCartLabel();

            $this->bundleSelectionsData[$selection->getSelectionId()] = [
                'isPreorder' => $isPreorder,
                'note' => $note,
                'cartLabel' => $cartLabel,
                'optionId' => $selection->getOptionId(),
            ];

            // Update option record
            $optionRecord = &$this->bundleOptionsData[$selection->getOptionId()];
            $optionRecord['selectionCount']++;
            $optionRecord['isSingle'] = $optionRecord['selectionCount'] == 1;

            if ($optionRecord['isSingle']) {
                $optionRecord['isPreorder'] = $isPreorder;
                $optionRecord['message'] = $note;
                $optionRecord['selectionId'] = $selection->getSelectionId();
            } else {
                // Have to analyze selections on frontend in order to find out
                $optionRecord['isPreorder'] = null;
                $optionRecord['message'] = null;
            }
        }

        return $this;
    }
}
