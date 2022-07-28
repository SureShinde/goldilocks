<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Model\InventorySales\ResourceModel;

/**
 * Stock ID resolver plugin
 */
class StockIdResolver
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
     * Around resolve
     *
     * @param \Magento\InventorySales\Model\ResourceModel\StockIdResolver $subject
     * @param \Closure $proceed
     * @param string $type
     * @param string $code
     * @return int|null
     */
    public function aroundResolve(
        \Magento\InventorySales\Model\ResourceModel\StockIdResolver $subject,
        \Closure $proceed,
        string $type,
        string $code
    )
    {
        if($type == \Magento\InventorySalesApi\Api\Data\SalesChannelInterface::TYPE_WEBSITE){
           return $proceed($type, $code);
        }
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from($this->resourceConnection->getTableName('ecombricks_store__inventory_stock_sales_channel'), 'stock_id')
            ->where(\Magento\InventorySalesApi\Api\Data\SalesChannelInterface::TYPE.' = ?', $type)
            ->where(\Magento\InventorySalesApi\Api\Data\SalesChannelInterface::CODE.' = ?', $code);
        $stockId = $connection->fetchOne($select);
        return false === $stockId ? null : (int) $stockId;
    }

}
