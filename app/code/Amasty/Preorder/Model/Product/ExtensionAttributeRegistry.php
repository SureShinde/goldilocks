<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Product;

use Amasty\Preorder\Api\Data\ProductInformationInterface;

class ExtensionAttributeRegistry
{
    /**
     * @var ProductInformationInterface[]
     */
    private $storage = [];

    public function get(string $sku, int $websiteId): ?ProductInformationInterface
    {
        return $this->storage[$websiteId][$sku] ?? null;
    }

    public function set(string $sku, int $websiteId, ProductInformationInterface $preorderProductInformation): void
    {
        $this->storage[$websiteId][$sku] = $preorderProductInformation;
    }

    public function resetStorage()
    {
        $this->storage = [];
    }
}
