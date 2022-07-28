<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Model\CatalogInventory\ResourceModel\Stock;

/**
 * Stock status resource plugin
 */
class Status
{

    /**
     * Add stock status to select
     *
     * @var \Magento\InventoryCatalog\Model\ResourceModel\AddStockStatusToSelect
     */
    protected $addStockStatusToSelect;

    /**
     * Default stock provider
     *
     * @var \Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface
     */
    protected $defaultStockProvider;

    /**
     * Get stock ID by store
     *
     * @var \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface
     */
    protected $getStockIdByStore;

    /**
     * Constructor
     *
     * @param \Magento\InventoryCatalog\Model\ResourceModel\AddStockStatusToSelect $addStockStatusToSelect
     * @param \Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface $defaultStockProvider
     * @param \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
     * @return void
     */
    public function __construct(
        \Magento\InventoryCatalog\Model\ResourceModel\AddStockStatusToSelect $addStockStatusToSelect,
        \Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface $defaultStockProvider,
        \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
    )
    {
        $this->addStockStatusToSelect = $addStockStatusToSelect;
        $this->defaultStockProvider = $defaultStockProvider;
        $this->getStockIdByStore = $getStockIdByStore;
    }

    /**
     * Around add stock status to select
     *
     * @param \Magento\CatalogInventory\Model\ResourceModel\Stock\Status $stockStatus
     * @param \Closure $proceed
     * @param \Magento\Framework\DB\Select $select
     * @param \Magento\Store\Model\Website $website
     * @return \Magento\CatalogInventory\Model\ResourceModel\Stock\Status
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundAddStockStatusToSelect(
        \Magento\CatalogInventory\Model\ResourceModel\Stock\Status $stockStatus,
        \Closure $proceed,
        \Magento\Framework\DB\Select $select,
        \Magento\Store\Model\Website $website
    )
    {
        $websiteCode = $website->getCode();
        if (null === $websiteCode) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Website code is empty'));
        }
        $store = $website->getDefaultStore();
        $stockId = $this->getStockIdByStore->execute($store);
        if ($this->defaultStockProvider->getId() === $stockId) {
            return $proceed($select, $website);
        } else {
            $this->addStockStatusToSelect->execute($select, $stockId);
        }
        return $stockStatus;
    }

}
