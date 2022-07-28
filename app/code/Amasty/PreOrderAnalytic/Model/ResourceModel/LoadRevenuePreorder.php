<?php

declare(strict_types=1);

namespace Amasty\PreOrderAnalytic\Model\ResourceModel;

use Amasty\Preorder\Api\Data\OrderInformationInterface;
use Amasty\Preorder\Api\Data\OrderItemInformationInterface;
use Magento\Framework\App\ResourceConnection;

class LoadRevenuePreorder
{
    const CURRENCY_CODE_COLUMN = 'base_currency_code';
    const REVENUE_COLUMN = 'revenue';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ApplyFilterParams
     */
    private $applyFilterParams;

    public function __construct(ResourceConnection $resourceConnection, ApplyFilterParams $applyFilterParams)
    {
        $this->resourceConnection = $resourceConnection;
        $this->applyFilterParams = $applyFilterParams;
    }

    public function execute(array $params): array
    {
        $connection = $this->resourceConnection->getConnection('sales');
        $select = $connection->select()->from(
            ['sales_order' => $this->resourceConnection->getTableName('sales_order')],
            [self::CURRENCY_CODE_COLUMN]
        )->join(
            ['sales_order_item' => $this->resourceConnection->getTableName('sales_order_item')],
            'sales_order.entity_id = sales_order_item.order_id',
            [self::REVENUE_COLUMN => 'SUM(base_row_total)']
        )->join(
            ['item_preorder' => $this->resourceConnection->getTableName(OrderItemInformationInterface::MAIN_TABLE)],
            'sales_order_item.item_id = item_preorder.order_item_id',
            []
        )->where(
            'sales_order_item.parent_item_id IS NULL'
        )->group(self::CURRENCY_CODE_COLUMN);
        $this->applyFilterParams->execute($select, $params);

        return $connection->fetchAll($select);
    }
}
