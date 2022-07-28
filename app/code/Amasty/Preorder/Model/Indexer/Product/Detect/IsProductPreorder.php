<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Indexer\Product\Detect;

use Amasty\Preorder\Model\Product\Detect\IsProductPreorderInterface;
use Magento\Catalog\Api\Data\ProductInterface;

class IsProductPreorder implements IsProductPreorderInterface
{
    /**
     * @var array
     */
    private $preorderMap = [];

    /**
     * @var \Amasty\Preorder\Model\Product\Detect\IsProductPreorder
     */
    private $isProductPreorder;

    public function __construct(\Amasty\Preorder\Model\Product\Detect\IsProductPreorder $isProductPreorder)
    {
        $this->isProductPreorder = $isProductPreorder;
    }

    public function execute(ProductInterface $product, float $requiredQty = 1): bool
    {
        if (!isset($this->preorderMap[$product->getStoreId()][$product->getId()])) {
            $product->setIsPreorder(null);
            $this->preorderMap[$product->getStoreId()][$product->getId()] = $this->isProductPreorder->execute(
                $product,
                $requiredQty
            );
        }

        return $this->preorderMap[$product->getStoreId()][$product->getId()];
    }
}
