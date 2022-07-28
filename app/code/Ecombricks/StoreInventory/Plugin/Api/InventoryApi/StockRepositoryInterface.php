<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Api\InventoryApi;

/**
 * Stock repository interface plugin
 */
class StockRepositoryInterface
{

    /**
     * Message manager
     *
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * Get assigned sales channels for stock
     *
     * @var \Magento\InventorySalesApi\Model\GetAssignedSalesChannelsForStockInterface
     */
    protected $getAssignedSalesChannelsForStock;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\InventorySalesApi\Model\GetAssignedSalesChannelsForStockInterface $getAssignedSalesChannelsForStock
     * @return void
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\InventorySalesApi\Model\GetAssignedSalesChannelsForStockInterface $getAssignedSalesChannelsForStock
    )
    {
        $this->messageManager = $messageManager;
        $this->getAssignedSalesChannelsForStock = $getAssignedSalesChannelsForStock;
    }

    /**
     * Get unassigned sales channels
     *
     * @param \Magento\InventoryApi\Api\Data\StockInterface $stock
     * @return \Magento\InventorySales\Model\SalesChannel[]
     */
    protected function getUnassignedSalesChannels(\Magento\InventoryApi\Api\Data\StockInterface $stock): array
    {
        $newStoreCodes = $result = [];
        $assignedSalesChannels = $this->getAssignedSalesChannelsForStock->execute((int) $stock->getStockId());
        $newSalesChannels = $stock->getExtensionAttributes()->getSalesChannels() ?: [];
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

    /**
     * After save
     *
     * @param \Magento\InventoryApi\Api\StockRepositoryInterface $subject
     * @param int $stockId
     * @param \Magento\InventoryApi\Api\Data\StockInterface $stock
     * @return int
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function afterSave(
        \Magento\InventoryApi\Api\StockRepositoryInterface $subject,
        int $stockId,
        \Magento\InventoryApi\Api\Data\StockInterface $stock
    ): int
    {
        $unassignedSalesChannels = $this->getUnassignedSalesChannels($stock);
        if (count($unassignedSalesChannels)) {
            $this->messageManager->addNoticeMessage(
                __('All unassigned sales channels will be assigned to the Default Stock')
            );
        }
        return $stockId;
    }

}
