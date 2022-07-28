<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Api\InventorySalesApi\Adminhtml;

/**
 * Get stock by sales channel interface plugin
 */
class GetStockBySalesChannelInterface
{

    /**
     * Get default stock
     *
     * @var \Ecombricks\StoreInventory\Api\InventorySalesApi\GetDefaultStockInterface
     */
    protected $getDefaultStock;

    /**
     * Constructor
     *
     * @param \Ecombricks\StoreInventory\Api\InventorySalesApi\GetDefaultStockInterface $getDefaultStock
     * @return void
     */
    public function __construct(
        \Ecombricks\StoreInventory\Api\InventorySalesApi\GetDefaultStockInterface $getDefaultStock
    )
    {
        $this->getDefaultStock = $getDefaultStock;
    }

    /**
     * Around execute
     *
     * @param \Magento\InventorySalesApi\Api\GetStockBySalesChannelInterface $subject
     * @param \Closure $proceed
     * @param \Magento\InventorySalesApi\Api\Data\SalesChannelInterface $salesChannel
     * @return \Magento\InventoryApi\Api\Data\StockInterface
     */
    public function aroundExecute(
        \Magento\InventorySalesApi\Api\GetStockBySalesChannelInterface $subject,
        \Closure $proceed,
        \Magento\InventorySalesApi\Api\Data\SalesChannelInterface $salesChannel
    ): \Magento\InventoryApi\Api\Data\StockInterface
    {
        if (
            \Ecombricks\StoreInventory\Api\InventorySalesApi\Data\SalesChannelInterface::TYPE_STORE === $salesChannel->getType() &&
            \Magento\Store\Model\Store::ADMIN_CODE === $salesChannel->getCode()
        ) {
            return $this->getDefaultStock->execute();
        }
        return $proceed($salesChannel);
    }

}
