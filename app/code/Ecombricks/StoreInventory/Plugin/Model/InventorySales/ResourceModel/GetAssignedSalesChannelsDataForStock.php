<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Model\InventorySales\ResourceModel;

/**
 * Get assigned sales channels data plugin
 */
class GetAssignedSalesChannelsDataForStock
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
     * @param \Magento\InventorySales\Model\ResourceModel\GetAssignedSalesChannelsDataForStock $subject
     * @param \Closure $proceed
     * @param int $stockId
     * @return array
     */
    public function aroundExecute(
        \Magento\InventorySales\Model\ResourceModel\GetAssignedSalesChannelsDataForStock $subject,
        \Closure $proceed,
        int $stockId
    ): array
    {
        $connection = $this->resourceConnection->getConnection();
        return $connection->fetchAll(
            $connection->select()
                ->from($this->resourceConnection->getTableName('ecombricks_store__inventory_stock_sales_channel'))
                ->where('stock_id = ?', $stockId)
        );
    }

}
