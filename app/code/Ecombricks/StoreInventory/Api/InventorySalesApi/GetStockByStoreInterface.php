<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Api\InventorySalesApi;

/**
 * Get stock by store interface
 */
interface GetStockByStoreInterface
{

    /**
     * Execute
     *
     * @param null|string|bool|int|\Magento\Store\Api\Data\StoreInterface $store
     * @return \Magento\InventoryApi\Api\Data\StockInterface
     */
    public function execute($store = null): \Magento\InventoryApi\Api\Data\StockInterface;

}
