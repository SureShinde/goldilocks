<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Product\Inventory;

use Amasty\Preorder\Model\Product\IsInventoryEnabled;
use Amasty\Preorder\Model\ResourceModel\Product\Inventory\LoadQtyByProductSkus;
use Magento\CatalogInventory\Api\StockRegistryInterface;

class ResolveQtyForProducts implements ResolveQtyForProductsInterface
{
    /**
     * @var IsInventoryEnabled
     */
    private $isInventoryEnabled;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var LoadQtyByProductSkus
     */
    private $loadQtyByProductSkus;

    /**
     * @var QtyStorage
     */
    private $qtyStorage;

    public function __construct(
        IsInventoryEnabled $isInventoryEnabled,
        StockRegistryInterface $stockRegistry,
        LoadQtyByProductSkus $loadQtyByProductSkus,
        QtyStorage $qtyStorage
    ) {
        $this->isInventoryEnabled = $isInventoryEnabled;
        $this->stockRegistry = $stockRegistry;
        $this->loadQtyByProductSkus = $loadQtyByProductSkus;
        $this->qtyStorage = $qtyStorage;
    }

    public function execute(array $productSkus, string $websiteCode): void
    {
        $productSkus = $this->filterSkus($productSkus);

        if ($this->isInventoryEnabled->execute()) {
            $this->qtyStorage->add(
                $this->loadQtyByProductSkus->execute($productSkus, $websiteCode)
            );
        } else {
            foreach ($productSkus as $productSku) {
                $stockItem = $this->stockRegistry->getStockItemBySku($productSku, $websiteCode);
                $this->qtyStorage->set($productSku, $stockItem->getQty());
            }
        }
    }

    private function filterSkus(array $productSkus): array
    {
        return array_filter($productSkus, function ($sku) {
            return !$this->qtyStorage->isExist($sku);
        });
    }
}
