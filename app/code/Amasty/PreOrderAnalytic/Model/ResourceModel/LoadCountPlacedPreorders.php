<?php

declare(strict_types=1);

namespace Amasty\PreOrderAnalytic\Model\ResourceModel;

use Amasty\Preorder\Api\Data\OrderInformationInterface;
use Magento\Framework\App\ResourceConnection;

class LoadCountPlacedPreorders
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
            ['sales_order' => $this->resourceConnection->getTableName('sales_order')],
            ['COUNT(*)']
        )->join(
            ['order_preorder' => $this->resourceConnection->getTableName(OrderInformationInterface::MAIN_TABLE)],
            'sales_order.entity_id = order_preorder.order_id',
            []
        );
        $this->applyFilterParams->execute($select, $params);

        return (int) $connection->fetchOne($select);
    }
}
