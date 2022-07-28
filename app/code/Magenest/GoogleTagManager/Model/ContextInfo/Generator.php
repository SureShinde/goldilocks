<?php

namespace Magenest\GoogleTagManager\Model\ContextInfo;

use Magento\GoogleTagManager\Block\ListJson as ContextBlock;

class Generator
{
    /**
     * @var \Magenest\GoogleTagManager\Helper\Collection
     */
    private $collectionHelper;

    public function __construct(
        \Magenest\GoogleTagManager\Helper\Collection $collectionHelper
    ) {
        $this->collectionHelper = $collectionHelper;
    }

    public function generate(ContextBlock $contextBlock)
    {
        $products = $contextBlock->getLoadedProductCollection();

        $contextBlock->checkCartItems();

        $contextData = [
            'category' => $contextBlock->getCurrentCategoryName(),
            'list' => $contextBlock->getCurrentListName(),
        ];

        $position = $this->collectionHelper->getOffset($products);

        return \array_map(function ($product) use (&$position, $contextData) {
            return \array_replace($contextData, [
                'id' => $product->getSku(),
                'name' => $product->getName(),
                'type' => $product->getTypeId(),
                'position' => ++$position,
            ]);
        }, $this->collectionHelper->getItems($products));
    }
}
