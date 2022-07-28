<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Product\Detect;

use Amasty\Preorder\Model\ConfigProvider;
use Amasty\Preorder\Model\Order\OrderProcessingFlag;
use Amasty\Preorder\Model\Order\ProductQty;
use Amasty\Preorder\Model\Product\Constants;
use Amasty\Preorder\Model\ResourceModel\Inventory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\Store\Model\StoreManager;

class IsSimplePreorder implements IsProductPreorderInterface
{
    /**
     * @var StockRegistry
     */
    private $stockRegistry;

    /**
     * @var Inventory
     */
    private $inventory;

    /**
     * @var OrderProcessingFlag
     */
    private $orderProcessingFlag;

    /**
     * @var ProductQty
     */
    private $productQty;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var StoreManager
     */
    private $storeManager;

    public function __construct(
        StockRegistry $stockRegistry,
        Inventory $inventory,
        OrderProcessingFlag $orderProcessingFlag,
        ProductQty $productQty,
        ConfigProvider $configProvider,
        StoreManager $storeManager
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->inventory = $inventory;
        $this->orderProcessingFlag = $orderProcessingFlag;
        $this->productQty = $productQty;
        $this->configProvider = $configProvider;
        $this->storeManager = $storeManager;
    }

    public function execute(ProductInterface $product, float $requiredQty = 1): bool
    {
        $result = false;

        /** @var \Magento\CatalogInventory\Model\Stock\Item $stockStatus */
        $stockItem = $this->stockRegistry->getStockItem($product->getId());

        if ($stockItem) {
            $isPreorder = $stockItem->getBackorders() == Constants::BACKORDERS_PREORDER_OPTION;
            $website = $this->storeManager->getStore($product->getStoreId())->getWebsite();
            $qtyStock = $this->inventory->getQty($product->getData('sku'), $website->getCode());

            if ($qtyStock !== null) {
                if ($this->orderProcessingFlag->isFlag()) {
                    $qtyStock += $this->productQty->getPlacedQty((int) $product->getId());
                }

                if ($this->configProvider->isAllowEmpty((int) $website->getId())) {
                    $disabledByQty = $this->configProvider->isDisableForPositiveQty((int) $website->getId())
                        && $qtyStock > 0
                        && ($qtyStock >= $requiredQty || $this->orderProcessingFlag->isFlag());
                } else {
                    $disabledByQty = $qtyStock < 1;
                }

                $result = $isPreorder && !$disabledByQty;
            }
        }

        return $result;
    }
}
