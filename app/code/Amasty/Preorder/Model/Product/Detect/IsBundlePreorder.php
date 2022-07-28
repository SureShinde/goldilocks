<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Product\Detect;

use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\Selection;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

class IsBundlePreorder implements IsProductPreorderInterface
{
    /**
     * @var IsSimplePreorder
     */
    private $isSimplePreorder;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    public function __construct(
        IsProductPreorderInterface $isSimplePreorder,
        ProductCollectionFactory $productCollectionFactory
    ) {
        $this->isSimplePreorder = $isSimplePreorder;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    public function execute(ProductInterface $product, float $requiredQty = 1): bool
    {
        $optionIds = $optionSelectionCounts = $optionPreorder = [];
        $options = $product->getTypeInstance()->getOptionsCollection($product);

        foreach ($options as $option) {
            /** @var Option $option */
            if (!$option->getRequired()) {
                continue;
            }

            $id = $option->getId();
            $optionIds[] = $id;
            $optionSelectionCounts[$id] = 0;
            $optionPreorder[$id] = true;
            $firstOption[$id] = true;
        }

        if (!$optionIds) {
            return false;
        }

        $selections = $product->getTypeInstance()->getSelectionsCollection($optionIds, $product);
        $products = $this->getProductCollectionBySelectionsCollection($selections->getItems());

        foreach ($selections as $selection) {
            /** @var Selection $selection */

            /** @var Product $product */
            $product = $products->getItemById($selection->getProductId());

            $isPreorder = $this->isSimplePreorder->execute($product, $requiredQty);
            $optionId = $selection->getOptionId();
            $optionSelectionCounts[$optionId]++;
            $isFirstOption = isset($firstOption[$optionId]) && $firstOption[$optionId];

            if (!$isPreorder
                && (!isset($firstOption[$optionId]) || $isFirstOption)
            ) {
                $optionPreorder[$optionId] = false;
            }

            if ($isFirstOption) {
                $firstOption[$optionId] = false;
            }
        }

        $result = false;
        foreach ($optionPreorder as $id => $isPreorder) {
            if ($isPreorder && $optionSelectionCounts[$id] > 0) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    /**
     * @param Selection[] $selections
     * @return ProductCollection
     */
    private function getProductCollectionBySelectionsCollection(array $selections): ProductCollection
    {
        $productIds = [];

        foreach ($selections as $selection) {
            /** @var Selection $selection */
            $productIds[] = $selection->getProductId();
        }

        /** @var ProductCollection $collection */
        $collection = $this->productCollectionFactory->create()
            ->addFieldToFilter('entity_id', ['in', $productIds]);

        return $collection;
    }
}
