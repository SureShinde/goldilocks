<?php

declare(strict_types=1);

namespace Amasty\PreOrderRelease\Model\ResourceModel;

use Amasty\Preorder\Api\Data\OrderItemInformationInterface;
use Magento\Framework\App\ResourceConnection;

class LoadCustomerBuyedPreorder
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(array $productIds, array $orderStatuses): array
    {
        $connectionName = 'sales';
        $salesConnection = $this->resourceConnection->getConnection($connectionName);
        $select = $salesConnection->select()->from(
            ['sales_order' => $this->resourceConnection->getTableName('sales_order', $connectionName)],
            ['customer_email', 'store_id']
        )->join(
            ['sales_order_item' => $this->resourceConnection->getTableName('sales_order_item', $connectionName)],
            'sales_order.entity_id = sales_order_item.order_id',
            ['product_id', 'name']
        )->join(
            ['preorder_item' => $this->resourceConnection->getTableName(
                OrderItemInformationInterface::MAIN_TABLE,
                $connectionName
            )],
            sprintf('sales_order_item.item_id = preorder_item.%s', OrderItemInformationInterface::ORDER_ITEM_ID),
            []
        )->where(
            'sales_order_item.product_id IN (?)',
            $productIds
        )->where(
            'sales_order.status IN (?)',
            $orderStatuses
        )->where(
            'parent_item_id IS NULL'
        )->group(
            'store_id'
        )->group(
            'product_id'
        )->group(
            'customer_email'
        );

        return $salesConnection->fetchAll($select);
    }
}
