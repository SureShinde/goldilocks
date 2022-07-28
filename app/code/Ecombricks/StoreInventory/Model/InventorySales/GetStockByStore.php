<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Model\InventorySales;

/**
 * Get stock by store
 */
class GetStockByStore implements \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockByStoreInterface
{

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Stock resolver
     *
     * @var \Magento\InventorySalesApi\Api\StockResolverInterface
     */
    protected $stockResolver;

    /**
     * Get default stock
     *
     * @var \Ecombricks\StoreInventory\Api\InventorySalesApi\GetDefaultStockInterface
     */
    protected $getDefaultStock;

    /**
     * Constructor
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\InventorySalesApi\Api\StockResolverInterface $stockResolver
     * @param \Ecombricks\StoreInventory\Api\InventorySalesApi\GetDefaultStockInterface $getDefaultStock
     * @return void
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\InventorySalesApi\Api\StockResolverInterface $stockResolver,
        \Ecombricks\StoreInventory\Api\InventorySalesApi\GetDefaultStockInterface $getDefaultStock
    )
    {
        $this->storeManager = $storeManager;
        $this->stockResolver = $stockResolver;
        $this->getDefaultStock = $getDefaultStock;
    }

    /**
     * Execute
     *
     * @param null|string|bool|int|\Magento\Store\Api\Data\StoreInterface $store
     * @return \Magento\InventoryApi\Api\Data\StockInterface
     */
    public function execute($store = null): \Magento\InventoryApi\Api\Data\StockInterface
    {
        try {
            $stock = $this->stockResolver->execute(
                \Ecombricks\StoreInventory\Api\InventorySalesApi\Data\SalesChannelInterface::TYPE_STORE,
                $this->storeManager->getStore($store)->getCode()
            );
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            $stock = $this->getDefaultStock->execute();
        }
        return $stock;
    }

}
