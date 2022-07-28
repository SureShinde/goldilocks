<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Product\Detect;

use Magento\Catalog\Api\Data\ProductInterface;

interface IsProductPreorderInterface
{
    public function execute(ProductInterface $product, float $requiredQty = 1): bool;
}
