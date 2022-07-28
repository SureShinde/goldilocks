<?php

declare(strict_types=1);

namespace Amasty\Preorder\Plugin\InventorySales\Model\IsProductSalableCondition\BackOrderCondition;

use Amasty\Preorder\Model\ConfigProvider;
use Amasty\Preorder\Model\Product\Constants;
use Amasty\Preorder\Model\ResourceModel\Inventory;
use Closure;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventorySales\Model\IsProductSalableCondition\BackOrderCondition as OriginalCondition;
use Magento\Store\Model\StoreManagerInterface;

class CheckForStock
{
    /**
     * @var StockRegistry
     */
    private $stockRegistry;

    /**
     * @var Inventory
     */
    private $inventoryResolver;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        Inventory $inventoryResolver,
        StockRegistry $stockRegistry,
        ConfigProvider $configProvider,
        StoreManagerInterface $storeManager
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->inventoryResolver = $inventoryResolver;
        $this->configProvider = $configProvider;
        $this->storeManager = $storeManager;
    }

    /**
     * @param OriginalCondition $subject
     * @param Closure $closure
     * @param string $sku
     * @param int $stockId
     * @return bool
     * @throws NoSuchEntityException
     *
     * @see \Magento\InventorySales\Model\IsProductSalableCondition\IsProductSalableConditionChain
     * Used on global scope di , not frontend.
     */
    public function aroundExecute(OriginalCondition $subject, Closure $closure, string $sku, int $stockId): bool
    {
        $websiteCode = $this->storeManager->getWebsite()->getCode();
        $stockItem = $this->stockRegistry->getStockItemBySku($sku, $stockId);

        if ($stockItem->getBackorders() == Constants::BACKORDERS_PREORDER_OPTION) {
            $isInThreshold = $this->inventoryResolver->getQty($sku, $websiteCode) <= $stockItem->getMinQty();
            $result = !(!$this->configProvider->isAllowEmpty() && $isInThreshold)
                && $this->inventoryResolver->getIsInStock($sku, $stockId);
        } else {
            $result = $closure($sku, $stockId);
        }

        return $result;
    }
}
