<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Product\RetrieveNote\FormatNote;

use Magento\Catalog\Api\Data\ProductInterface;

interface CustomResolverInterface
{
    public function execute(ProductInterface $product): string;
}
