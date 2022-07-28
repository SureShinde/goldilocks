<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Model\InventoryCatalog;

/**
 * Get stock ID for current website plugin
 */
class GetStockIdForCurrentWebsite
{

    /**
     * Get stock ID by store
     *
     * @var \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface
     */
    protected $getStockIdByStore;

    /**
     * Constructor
     *
     * @param \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
     * @return void
     */
    public function __construct(
        \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
    )
    {
        $this->getStockIdByStore = $getStockIdByStore;
    }

    /**
     * Around execute
     *
     * @param \Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite $subject
     * @param \Closure $proceed
     * @return int
     */
    public function aroundExecute(
        \Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite $subject,
        \Closure $proceed
    ): int
    {
        return $this->getStockIdByStore->execute();
    }

}
