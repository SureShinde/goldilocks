<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Model\InventorySales;

/**
 * Get unassigned sales channels for stock plugin
 */
class GetUnassignedSalesChannelsForStock
{

    /**
     * Get assigned sales channels for stock
     *
     * @var \Magento\InventorySalesApi\Model\GetAssignedSalesChannelsForStockInterface
     */
    protected $getAssignedSalesChannelsForStock;

    /**
     * Constructor
     *
     * @param \Magento\InventorySalesApi\Model\GetAssignedSalesChannelsForStockInterface $getAssignedSalesChannelsForStock
     * @return void
     */
    public function __construct(
        \Magento\InventorySalesApi\Model\GetAssignedSalesChannelsForStockInterface $getAssignedSalesChannelsForStock
    )
    {
        $this->getAssignedSalesChannelsForStock = $getAssignedSalesChannelsForStock;
    }

    /**
     * Around execute
     *
     * @param \Magento\InventorySales\Model\GetUnassignedSalesChannelsForStock $subject
     * @param \Closure $proceed
     * @return array
     */
    public function aroundExecute(
        \Magento\InventorySales\Model\GetUnassignedSalesChannelsForStock $subject,
        \Closure $proceed,
        \Magento\InventoryApi\Api\Data\StockInterface $stock
    ): array
    {
        $newStoreCodes = $result = [];
        $assignedSalesChannels = $this->getAssignedSalesChannelsForStock->execute((int) $stock->getStockId());
        $extensionAttributes = $stock->getExtensionAttributes();
        $newSalesChannels = $extensionAttributes->getSalesChannels() ?: [];
        foreach ($newSalesChannels as $salesChannel) {
            if ($salesChannel->getType() === \Ecombricks\StoreInventory\Api\InventorySalesApi\Data\SalesChannelInterface::TYPE_STORE) {
                $newStoreCodes[] = $salesChannel->getCode();
            }
        }
        foreach ($assignedSalesChannels as $salesChannel) {
            if (
                $salesChannel->getType() === \Ecombricks\StoreInventory\Api\InventorySalesApi\Data\SalesChannelInterface::TYPE_STORE &&
                !in_array($salesChannel->getCode(), $newStoreCodes, true)
            ) {
                $result[] = $salesChannel;
            }
        }
        return $result;
    }

}
