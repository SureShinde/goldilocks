<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel;

use Amasty\DeliveryDateManager\Api\Data\TimeIntervalChannelRelationInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class TimeIntervalChannelRelation extends AbstractDb
{
    public const MAIN_TABLE = 'amasty_deliverydate_time_interval_delivery_channel';

    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, TimeIntervalChannelRelationInterface::RELATION_ID);
    }

    public function insertArray(array $data): void
    {
        $this->getConnection()->insertArray(
            $this->getMainTable(),
            [
                TimeIntervalChannelRelationInterface::DELIVERY_CHANNEL_ID,
                TimeIntervalChannelRelationInterface::TIME_INTERVAL_ID
            ],
            $data
        );
    }
}
