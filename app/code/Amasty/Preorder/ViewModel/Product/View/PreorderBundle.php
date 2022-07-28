<?php

declare(strict_types=1);

namespace Amasty\Preorder\ViewModel\Product\View;

use Amasty\Preorder\Model\Map\Product\Type\Bundle as BundleMapper;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class PreorderBundle implements ArgumentInterface
{
    /**
     * @var BundleMapper
     */
    private $bundleMapper;

    /**
     * @var Json
     */
    private $serializer;

    public function __construct(BundleMapper $bundleMapper, Json $serializer)
    {
        $this->bundleMapper = $bundleMapper;
        $this->serializer = $serializer;
    }

    public function isAllProductsPreorder(ProductInterface $product): bool
    {
        $this->bundleMapper->setProduct($product);
        return $this->bundleMapper->getIsAllProductsPreorder();
    }

    public function getMap(ProductInterface $product): string
    {
        $selectionsPreorderMap = [];
        $this->bundleMapper->setProduct($product);
        $selections = $this->bundleMapper->getBundleSelectionsData();
        foreach ($selections as $selectionId => $selection) {
            if (!$selection['isPreorder']) {
                continue;
            }
            $selectionsPreorderMap[$selection['optionId'] . '-' . $selectionId] = $selection['note'];
        }

        return $this->serializer->serialize($selectionsPreorderMap);
    }

    public function getBundleOptionsData(ProductInterface $product): string
    {
        $this->bundleMapper->setProduct($product);
        return $this->serializer->serialize($this->bundleMapper->getBundleOptionsData());
    }
}
