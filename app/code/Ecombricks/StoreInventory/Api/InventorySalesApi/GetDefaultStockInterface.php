<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Api\InventorySalesApi;

/**
 * Get default stock interface
 */
interface GetDefaultStockInterface
{

    /**
     * Execute
     *
     * @return \Magento\InventoryApi\Api\Data\StockInterface
     */
    public function execute(): \Magento\InventoryApi\Api\Data\StockInterface;

}
