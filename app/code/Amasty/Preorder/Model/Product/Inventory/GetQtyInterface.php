<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Product\Inventory;

interface GetQtyInterface
{
    public function execute(string $productSku, string $websiteCode): float;
}
