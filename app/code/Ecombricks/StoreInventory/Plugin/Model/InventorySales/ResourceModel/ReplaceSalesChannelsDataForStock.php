<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Model\InventorySales\ResourceModel;

/**
 * Replace sales channels data plugin
 */
class ReplaceSalesChannelsDataForStock
{

    /**
     * Resource connection
     *
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection
    )
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Around execute
     *
     * @param \Magento\InventorySales\Model\ResourceModel\ReplaceSalesChannelsDataForStock $subject
     * @param \Closure $proceed
     * @param array $salesChannels
     * @param int $stockId
     * @return void
     */
    public function aroundExecute(
        \Magento\InventorySales\Model\ResourceModel\ReplaceSalesChannelsDataForStock $subject,
        \Closure $proceed,
        array $salesChannels,
        int $stockId
    ): void
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('ecombricks_store__inventory_stock_sales_channel');
        $connection->delete($tableName, ['stock_id = ?' => $stockId]);
        if (!count($salesChannels)) {
            return;
        }
        $salesChannelsData = [];
        foreach ($salesChannels as $salesChannel) {
            $salesChannelsData[] = [
                \Magento\InventorySalesApi\Api\Data\SalesChannelInterface::TYPE => $salesChannel->getType(),
                \Magento\InventorySalesApi\Api\Data\SalesChannelInterface::CODE => $salesChannel->getCode(),
                'stock_id' => $stockId,
            ];
        }
        $connection->insertOnDuplicate($tableName, $salesChannelsData);
    }

}
