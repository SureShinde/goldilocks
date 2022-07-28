<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Product\Detect;

use Magento\Catalog\Api\Data\ProductInterface;

class IsProductPreorder implements IsProductPreorderInterface
{
    /**
     * @var IsProductPreorderInterface
     */
    private $isCompositeProductPreorder;

    /**
     * @var IsProductPreorderInterface
     */
    private $isSimplePreorder;

    public function __construct(
        IsProductPreorderInterface $isCompositeProductPreorder,
        IsProductPreorderInterface $isSimplePreorder
    ) {
        $this->isCompositeProductPreorder = $isCompositeProductPreorder;
        $this->isSimplePreorder = $isSimplePreorder;
    }

    public function execute(ProductInterface $product, float $requiredQty = 1): bool
    {
        if ($product->isComposite()) {
            $result = $this->isCompositeProductPreorder->execute($product, $requiredQty);
        } else {
            $result = $this->isSimplePreorder->execute($product, $requiredQty);
        }

        return $result;
    }
}
