<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\ResourceModel\Order\Grid\Collection;

use Amasty\Preorder\Api\Data\OrderInformationInterface;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection;

class JoinPreorderData
{
    public const JOINED_PREORDER_DATA_FLAG = 'preorder_data_joined';

    public function execute(Collection $collection): void
    {
        if (!$collection->isLoaded() && !$collection->hasFlag(self::JOINED_PREORDER_DATA_FLAG)) {
            $collection->setFlag(self::JOINED_PREORDER_DATA_FLAG, true);
            $collection->getSelect()->joinLeft(
                ['preorder' => $collection->getTable(OrderInformationInterface::MAIN_TABLE)],
                sprintf('preorder.%s = main_table.entity_id', OrderInformationInterface::ORDER_ID),
                [
                    'is_preorder' => $collection->getConnection()->getCheckSql(
                        sprintf('preorder.%s IS NULL', OrderInformationInterface::PREORDER_FLAG),
                        0,
                        sprintf('preorder.%s', OrderInformationInterface::PREORDER_FLAG)
                    )
                ]
            );
        }
    }
}
