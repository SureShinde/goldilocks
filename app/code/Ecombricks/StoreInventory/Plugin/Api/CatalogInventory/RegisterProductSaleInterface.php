<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Api\CatalogInventory;

/**
 * Register product sale interface plugin
 */
class RegisterProductSaleInterface
{

    /**
     * Get SKUs by product IDs
     *
     * @var \Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface
     */
    protected $getSkusByProductIds;

    /**
     * Get stock ID by store
     *
     * @var \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface
     */
    protected $getStockIdByStore;

    /**
     * Check items quantity
     *
     * @var \Magento\InventorySales\Model\CheckItemsQuantity
     */
    protected $checkItemsQuantity;

    /**
     * Get product types by SKUs
     *
     * @var \Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface
     */
    protected $getProductTypesBySkus;

    /**
     * Is source item management allowed for product type
     *
     * @var \Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface
     */
    protected $isSourceItemManagementAllowedForProductType;

    /**
     * Constructor
     *
     * @param \Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface $getSkusByProductIds
     * @param \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
     * @param \Magento\InventorySales\Model\CheckItemsQuantity $checkItemsQuantity
     * @param \Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface $getProductTypesBySkus
     * @param \Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
     * @return void
     */
    public function __construct(
        \Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface $getSkusByProductIds,
        \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore,
        \Magento\InventorySales\Model\CheckItemsQuantity $checkItemsQuantity,
        \Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface $getProductTypesBySkus,
        \Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
    )
    {
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->getStockIdByStore = $getStockIdByStore;
        $this->checkItemsQuantity = $checkItemsQuantity;
        $this->getProductTypesBySkus = $getProductTypesBySkus;
        $this->isSourceItemManagementAllowedForProductType = $isSourceItemManagementAllowedForProductType;
    }

    /**
     * Around register products sale
     *
     * @param \Magento\CatalogInventory\Api\RegisterProductSaleInterface $subject
     * @param \Closure $proceed
     * @param float[] $items
     * @param int|null $storeId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundRegisterProductsSale(
        \Magento\CatalogInventory\Api\RegisterProductSaleInterface $subject,
        \Closure $proceed,
        $items,
        $storeId = null
    )
    {
        if (empty($items)) {
            return [];
        }
        if (null === $storeId) {
            throw new \Magento\Framework\Exception\LocalizedException(__('$storeId parameter is required'));
        }
        $skus = $this->getSkusByProductIds->execute(array_keys($items));
        $productTypes = $this->getProductTypesBySkus->execute($skus);
        $itemsBySku = [];
        foreach ($skus as $productId => $sku) {
            if (false === $this->isSourceItemManagementAllowedForProductType->execute($productTypes[$sku])) {
                continue;
            }
            $itemsBySku[$sku] = $items[$productId];
        }
        $this->checkItemsQuantity->execute($itemsBySku, $this->getStockIdByStore->execute($storeId));
        return [];
    }

}
