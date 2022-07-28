<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Product\Detect;

use Magento\Catalog\Api\Data\ProductInterface;

class IsGroupedPreorder implements IsProductPreorderInterface
{
    /**
     * @var IsSimplePreorder
     */
    private $isSimplePreorder;

    public function __construct(IsProductPreorderInterface $isSimplePreorder)
    {
        $this->isSimplePreorder = $isSimplePreorder;
    }

    public function execute(ProductInterface $product, float $requiredQty = 1): bool
    {
        $elementaryProducts = $product->getTypeInstance()->getAssociatedProducts($product);

        if (count($elementaryProducts) == 0) {
            return false;
        }

        $result = true;
        foreach ($elementaryProducts as $elementary) {
            if (!$this->isSimplePreorder->execute($elementary, $requiredQty)) {
                $result = false;
                break;
            }
        }

        return $result;
    }
}
