<?php

declare(strict_types=1);

namespace Amasty\PreOrderAnalytic\Model\ResourceModel;

use Amasty\Preorder\Api\Data\OrderItemInformationInterface;
use Magento\Framework\App\ResourceConnection;

class LoadBestsellerPreorders
{
    const SALES_CONNECTION = 'sales';

    const PRODUCT_ID_COLUMN = 'product_id';
    const REVENUE_COLUMN = 'revenue';
    const CURRENCY_CODE_COLUMN = 'base_currency_code';
    const NAME_COLUMN = 'name';
    const QTY_COLUMN = 'qty';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(int $limit): array
    {
        $connection = $this->resourceConnection->getConnection(self::SALES_CONNECTION);
        $select = $connection->select()->from(
            ['sales_order_item' => $this->resourceConnection->getTableName(
                'sales_order_item',
                self::SALES_CONNECTION
            )],
            [
                self::PRODUCT_ID_COLUMN,
                self::NAME_COLUMN,
                self::QTY_COLUMN => 'SUM(qty_ordered)',
                self::REVENUE_COLUMN => 'SUM(base_row_total)'
            ]
        )->join(
            ['item_preorder' => $this->resourceConnection->getTableName(
                OrderItemInformationInterface::MAIN_TABLE,
                self::SALES_CONNECTION
            )],
            'sales_order_item.item_id = item_preorder.order_item_id',
            []
        )->join(
            ['sales_order' => $this->resourceConnection->getTableName(
                'sales_order_grid',
                self::SALES_CONNECTION
            )],
            'sales_order.entity_id = sales_order_item.order_id',
            [self::CURRENCY_CODE_COLUMN]
        )->where(
            'sales_order_item.parent_item_id IS NULL'
        )->group(
            'product_id'
        )->group(
            'base_currency_code'
        )->limit($limit);

        return $connection->fetchAll($select);
    }
}
