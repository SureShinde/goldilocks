<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Api\InventorySalesApi;

/**
 * Get stock ID by store interface
 */
interface GetStockIdByStoreInterface
{

    /**
     * Execute
     *
     * @param null|string|bool|int|\Magento\Store\Api\Data\StoreInterface $store
     * @return int
     */
    public function execute($store = null): int;

}
