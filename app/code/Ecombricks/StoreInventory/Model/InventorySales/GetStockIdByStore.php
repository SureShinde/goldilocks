<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Model\InventorySales;

/**
 * Get stock ID by store
 */
class GetStockIdByStore implements \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface
{

    /**
     * Stock resolver
     *
     * @var \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockByStoreInterface
     */
    protected $getStockByStore;

    /**
     * Constructor
     *
     * @param \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockByStoreInterface $getStockByStore
     * @return void
     */
    public function __construct(
        \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockByStoreInterface $getStockByStore
    )
    {
        $this->getStockByStore = $getStockByStore;
    }

    /**
     * Execute
     *
     * @param null|string|bool|int|\Magento\Store\Api\Data\StoreInterface $store
     * @return int
     */
    public function execute($store = null): int
    {
        return (int) $this->getStockByStore->execute($store)->getStockId();
    }

}
