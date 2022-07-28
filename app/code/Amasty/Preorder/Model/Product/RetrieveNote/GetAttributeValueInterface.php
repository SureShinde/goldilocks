<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Product\RetrieveNote;

use Magento\Catalog\Api\Data\ProductInterface;

interface GetAttributeValueInterface
{
    public function execute(ProductInterface $product): string;
}
