<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Api\CatalogInventory;

/**
 * Catalog inventory stock registry interface plugin
 */
class StockRegistryInterface
{

    /**
     * Get stock ID by store
     *
     * @var \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface
     */
    protected $getStockIdByStore;

    /**
     * Get SKUs by product IDs
     *
     * @var \Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface
     */
    protected $getSkusByProductIds;

    /**
     * Get product salable qty
     *
     * @var \Magento\InventorySalesApi\Api\GetProductSalableQtyInterface
     */
    protected $getProductSalableQty;

    /**
     * Is product salable
     *
     * @var \Magento\InventorySalesApi\Api\IsProductSalableInterface
     */
    protected $isProductSalable;

    /**
     * Constructor
     *
     * @param \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
     * @param \Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface $getSkusByProductIds
     * @param \Magento\InventorySalesApi\Api\GetProductSalableQtyInterface $getProductSalableQty
     * @param \Magento\InventorySalesApi\Api\IsProductSalableInterface $isProductSalable
     * @return void
     */
    public function __construct(
        \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore,
        \Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface $getSkusByProductIds,
        \Magento\InventorySalesApi\Api\GetProductSalableQtyInterface $getProductSalableQty,
        \Magento\InventorySalesApi\Api\IsProductSalableInterface $isProductSalable
    )
    {
        $this->getStockIdByStore = $getStockIdByStore;
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->getProductSalableQty = $getProductSalableQty;
        $this->isProductSalable = $isProductSalable;
    }

    /**
     * Around get product stock status by SKU
     *
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $subject
     * @param \Closure $proceed
     * @param string $productSku
     * @param int $storeId
     * @return int
     */
    public function aroundGetProductStockStatusBySku(
        \Magento\CatalogInventory\Api\StockRegistryInterface $subject,
        \Closure $proceed,
        $productSku,
        $storeId = null
    ): int
    {
        return (int) $this->isProductSalable->execute(
            $productSku,
            $this->getStockIdByStore->execute($storeId)
        );
    }

    /**
     * Around get product stock status
     *
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $subject
     * @param \Closure $proceed
     * @param int $productId
     * @param int $storeId
     * @return int
     */
    public function aroundGetProductStockStatus(
        \Magento\CatalogInventory\Api\StockRegistryInterface $subject,
        \Closure $proceed,
        $productId,
        $storeId = null
    ): int
    {
        return (int) $this->isProductSalable->execute(
            $this->getSkusByProductIds->execute([$productId])[$productId],
            $this->getStockIdByStore->execute($storeId)
        );
    }

    /**
     * After get stock status by SKU
     *
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $subject
     * @param \Magento\CatalogInventory\Api\Data\StockStatusInterface $stockStatus
     * @param string $productSku
     * @param int $storeId
     * @return \Magento\CatalogInventory\Api\Data\StockStatusInterface
     */
    public function afterGetStockStatusBySku(
        \Magento\CatalogInventory\Api\StockRegistryInterface $subject,
        \Magento\CatalogInventory\Api\Data\StockStatusInterface $stockStatus,
        $productSku,
        $storeId = null
    ): \Magento\CatalogInventory\Api\Data\StockStatusInterface
    {
        $stockId = $this->getStockIdByStore->execute($storeId);
        $status = (int) $this->isProductSalable->execute($productSku, $stockId);
        try {
            $qty = $this->getProductSalableQty->execute($productSku, $stockId);
        } catch (\Magento\Framework\Exception\InputException $exception) {
            $qty = 0;
        }
        $stockStatus->setStockStatus($status);
        $stockStatus->setQty($qty);
        return $stockStatus;
    }

    /**
     * After get stock status
     *
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $subject
     * @param \Magento\CatalogInventory\Api\Data\StockStatusInterface $stockStatus
     * @param int $productId
     * @param int $storeId
     * @return \Magento\CatalogInventory\Api\Data\StockStatusInterface
     */
    public function afterGetStockStatus(
        \Magento\CatalogInventory\Api\StockRegistryInterface $subject,
        \Magento\CatalogInventory\Api\Data\StockStatusInterface $stockStatus,
        $productId,
        $storeId = null
    ): \Magento\CatalogInventory\Api\Data\StockStatusInterface
    {
        $sku = $this->getSkusByProductIds->execute([$productId])[$productId];
        $stockId = $this->getStockIdByStore->execute($storeId);
        $status = (int) $this->isProductSalable->execute($sku, $stockId);
        try {
            $qty = $this->getProductSalableQty->execute($sku, $stockId);
        } catch (\Magento\Framework\Exception\InputException $exception) {
            $qty = 0;
        }
        $stockStatus->setStockStatus($status);
        $stockStatus->setQty($qty);
        return $stockStatus;
    }

}
