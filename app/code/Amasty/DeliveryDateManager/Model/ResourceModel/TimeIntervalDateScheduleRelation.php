<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel;

use Amasty\DeliveryDateManager\Api\Data\TimeIntervalDateScheduleRelationInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class TimeIntervalDateScheduleRelation extends AbstractDb
{
    public const MAIN_TABLE = 'amasty_deliverydate_time_interval_date_schedule';

    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, TimeIntervalDateScheduleRelationInterface::RELATION_ID);
    }

    public function insertArray(array $data): void
    {
        $this->getConnection()->insertArray(
            $this->getMainTable(),
            [
                TimeIntervalDateScheduleRelationInterface::DATE_SCHEDULE_ID,
                TimeIntervalDateScheduleRelationInterface::TIME_INTERVAL_ID
            ],
            $data
        );
    }
}
