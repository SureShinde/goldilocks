<?php

declare(strict_types=1);

namespace Amasty\Preorder\ViewModel\Product\View;

use Amasty\Preorder\Model\Product\GetPreorderInformation;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedType;

class PreorderGrouped implements ArgumentInterface
{
    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var GetPreorderInformation
     */
    private $getPreorderInformation;

    public function __construct(Json $serializer, GetPreorderInformation $getPreorderInformation)
    {
        $this->serializer = $serializer;
        $this->getPreorderInformation = $getPreorderInformation;
    }

    public function getMap(ProductInterface $product): string
    {
        $map = [];
        foreach ($this->getAssociatedProducts($product) as $product) {
            $preorderInformation = $this->getPreorderInformation->execute($product);
            if ($preorderInformation->isPreorder()) {
                $map[$product->getId()] = [
                    'cartLabel' => $preorderInformation->getCartLabel(),
                    'note' => $preorderInformation->getNote(),
                    'preorder' => true
                ];
            }
        }

        return $this->serializer->serialize($map);
    }

    /**
     * @param ProductInterface|Product $product
     * @return Product[]
     */
    private function getAssociatedProducts(ProductInterface $product): array
    {
        /** @var GroupedType $typeInstance */
        $typeInstance = $product->getTypeInstance();
        return $typeInstance->getAssociatedProducts($product);
    }
}
