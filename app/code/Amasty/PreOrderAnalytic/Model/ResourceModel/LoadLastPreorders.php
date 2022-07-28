<?php

declare(strict_types=1);

namespace Amasty\PreOrderAnalytic\Model\ResourceModel;

use Amasty\Preorder\Api\Data\OrderItemInformationInterface;
use Magento\Framework\App\ResourceConnection;

class LoadLastPreorders
{
    const DEFAULT_LIMIT = 10;

    const CURRENCY_CODE = 'base_currency_code';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(int $limit = self::DEFAULT_LIMIT): array
    {
        $connection = $this->resourceConnection->getConnection('sales');
        $select = $connection->select()->from(
            ['sales_order_item' => $this->resourceConnection->getTableName('sales_order_item')],
            [
                'increment_id' => 'sales_order.increment_id',
                'customer_name' => 'sales_order.customer_name',
                'status' => 'sales_order.status',
                'created_at' => 'sales_order.created_at',
                'preorder_count' => 'COUNT(sales_order_item.item_id)',
                'preorder_revenue' => 'SUM(sales_order_item.base_row_total)',
                self::CURRENCY_CODE => 'sales_order.base_currency_code'
            ]
        )->join(
            ['sales_order' => $this->resourceConnection->getTableName('sales_order_grid')],
            'sales_order.entity_id = sales_order_item.order_id',
            []
        )->join(
            ['item_preorder' => $this->resourceConnection->getTableName(OrderItemInformationInterface::MAIN_TABLE)],
            'sales_order_item.item_id = item_preorder.order_item_id',
            []
        )->where(
            'sales_order_item.parent_item_id IS NULL'
        )->group(
            'sales_order.entity_id'
        )->order(
            'sales_order.created_at desc'
        )->limit($limit);

        return $connection->fetchAll($select);
    }
}
