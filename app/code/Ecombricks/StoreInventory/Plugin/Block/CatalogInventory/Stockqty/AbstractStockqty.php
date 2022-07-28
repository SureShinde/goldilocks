<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Block\CatalogInventory\Stockqty;

/**
 * Abstract stock quantity block plugin
 */
class AbstractStockqty
{

    /**
     * Get stock ID by store
     *
     * @var \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface
     */
    protected $getStockIdByStore;

    /**
     * Stock registry
     *
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * Get stock item configuration
     *
     * @var \Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface
     */
    protected $getStockItemConfiguration;

    /**
     * Get product salable qty
     *
     * @var \Magento\InventorySalesApi\Api\GetProductSalableQtyInterface
     */
    protected $getProductSalableQty;

    /**
     * Is source item management allowed for product type
     *
     * @var \Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface
     */
    protected $isSourceItemManagementAllowedForProductType;

    /**
     * Constructor
     *
     * @param \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface $getStockItemConfiguration
     * @param \Magento\InventorySalesApi\Api\GetProductSalableQtyInterface $getProductSalableQty
     * @param \Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
     * @return void
     */
    public function __construct(
        \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface $getStockItemConfiguration,
        \Magento\InventorySalesApi\Api\GetProductSalableQtyInterface $getProductSalableQty,
        \Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
    )
    {
        $this->getStockIdByStore = $getStockIdByStore;
        $this->stockRegistry = $stockRegistry;
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->getProductSalableQty = $getProductSalableQty;
        $this->isSourceItemManagementAllowedForProductType = $isSourceItemManagementAllowedForProductType;
    }

    /**
     * Around get product stock quantity
     *
     * @param \Magento\CatalogInventory\Block\Stockqty\AbstractStockqty $subject
     * @param \Closure $proceed
     * @param \Magento\Catalog\Model\Product $product
     * @return float
     */
    public function aroundGetProductStockQty(
        \Magento\CatalogInventory\Block\Stockqty\AbstractStockqty $subject,
        \Closure $proceed,
        $product
    )
    {
        return $this->stockRegistry->getStockStatus($product->getId(), $product->getStore()->getId())->getQty();
    }

    /**
     * Around is msg visible
     *
     * @param \Magento\CatalogInventory\Block\Stockqty\AbstractStockqty $subject
     * @param \Closure $proceed
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundIsMsgVisible(
        \Magento\CatalogInventory\Block\Stockqty\AbstractStockqty $subject,
        \Closure $proceed
    ): bool
    {
        $product = $subject->getProduct();
        if (!$this->isSourceItemManagementAllowedForProductType->execute($product->getTypeId())) {
            return false;
        }
        $sku = $product->getSku();
        $stockId = $this->getStockIdByStore->execute($product->getStore());
        $stockItemConfig = $this->getStockItemConfiguration->execute($sku, $stockId);
        $productSalableQty = $this->getProductSalableQty->execute($sku, $stockId);
        return (
                $stockItemConfig->getBackorders() === \Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface::BACKORDERS_NO ||
                $stockItemConfig->getBackorders() !== \Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface::BACKORDERS_NO && $stockItemConfig->getMinQty() < 0
            ) &&
            $productSalableQty <= $stockItemConfig->getStockThresholdQty() &&
            $productSalableQty > 0;
    }

    /**
     * Around get stock qty left
     *
     * @param \Magento\CatalogInventory\Block\Stockqty\AbstractStockqty $subject
     * @param \Closure $proceed
     * @return float
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundGetStockQtyLeft(
        \Magento\CatalogInventory\Block\Stockqty\AbstractStockqty $subject,
        \Closure $proceed
    ): float
    {
        $product = $subject->getProduct();
        return $this->getProductSalableQty->execute(
            $product->getSku(),
            $this->getStockIdByStore->execute($product->getStore())
        );
    }

}
