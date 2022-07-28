<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Model\InventorySales\ResourceModel;

/**
 * Delete sales channels plugin
 */
class DeleteSalesChannelToStockLink
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
     * @param \Magento\InventorySales\Model\ResourceModel\DeleteSalesChannelToStockLink $subject
     * @param \Closure $proceed
     * @param string $type
     * @param string $code
     * @return void
     */
    public function aroundExecute(
        \Magento\InventorySales\Model\ResourceModel\DeleteSalesChannelToStockLink $subject,
        \Closure $proceed,
        string $type,
        string $code
    ): void
    {
        $connection = $this->resourceConnection->getConnection();
        $connection->delete($this->resourceConnection->getTableName('ecombricks_store__inventory_stock_sales_channel'), [
            \Magento\InventorySalesApi\Api\Data\SalesChannelInterface::TYPE.' = ?' => $type,
            \Magento\InventorySalesApi\Api\Data\SalesChannelInterface::CODE.' = ?' => $code,
        ]);
    }

}
