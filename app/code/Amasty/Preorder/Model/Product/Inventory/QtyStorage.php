<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Product\Inventory;

class QtyStorage
{
    private $storage = [];

    public function set(string $productSku, ?float $qty): void
    {
        $this->storage[$productSku] = $qty;
    }

    public function add(array $qtys): void
    {
        $this->storage += $qtys;
    }

    public function get(string $productSku): ?float
    {
        return $this->storage[$productSku] ?? null;
    }

    public function isExist(string $productSku): bool
    {
        return array_key_exists($productSku, $this->storage);
    }
}
