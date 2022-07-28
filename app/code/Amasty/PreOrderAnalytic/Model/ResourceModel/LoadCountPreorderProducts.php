<?php

declare(strict_types=1);

namespace Amasty\PreOrderAnalytic\Model\ResourceModel;

use Amasty\Preorder\Api\Data\OrderItemInformationInterface;
use Magento\Framework\App\ResourceConnection;

class LoadCountPreorderProducts
{
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

    public function execute(array $params = []): int
    {
        $connection = $this->resourceConnection->getConnection('sales');
        $select = $connection->select()->from(
            ['sales_order_item' => $this->resourceConnection->getTableName('sales_order_item')],
            ['SUM(qty_ordered)']
        )->join(
            ['item_preorder' => $this->resourceConnection->getTableName(OrderItemInformationInterface::MAIN_TABLE)],
            'sales_order_item.item_id = item_preorder.order_item_id',
            []
        )->where('parent_item_id IS NULL');
        $this->applyFilterParams->execute($select, $params);

        return (int) $connection->fetchOne($select);
    }
}
