<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Product\Inventory;

interface ResolveQtyForProductsInterface
{
    public function execute(array $productSkus, string $websiteCode): void;
}
