<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Api\InventorySalesApi\Data;

/**
 * Inventory sales channel interface
 */
interface SalesChannelInterface extends \Magento\InventorySalesApi\Api\Data\SalesChannelInterface
{

    /**
     * Sales channel types
     */
    const TYPE_STORE = 'store';

}
