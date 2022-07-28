<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel;

use Amasty\DeliveryDateManager\Api\Data\DateScheduleChannelRelationInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class DateScheduleChannelRelation extends AbstractDb
{
    public const MAIN_TABLE = 'amasty_deliverydate_date_schedule_delivery_channel';

    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, 'relation_id');
    }

    /**
     * @param array $channelIds
     * @return array
     */
    public function getChannelRelationByChannelIds(array $channelIds): array
    {
        $select = $this->getConnection()->select()
            ->from($this->getTable(self::MAIN_TABLE))
            ->where('delivery_channel_id IN (?)', $channelIds);

        return $this->getConnection()->fetchAll($select);
    }

    /**
     * @param array $dateIds
     * @return array
     */
    public function getChannelRelationByDataIds(array $dateIds): array
    {
        $select = $this->getConnection()->select()
            ->from($this->getTable(self::MAIN_TABLE))
            ->where('date_schedule_id IN (?)', $dateIds);

        return $this->getConnection()->fetchAll($select);
    }

    /**
     * @param array $data
     */
    public function insertArray(array $data): void
    {
        $this->getConnection()->insertArray(
            $this->getMainTable(),
            [
                DateScheduleChannelRelationInterface::DELIVERY_CHANNEL_ID,
                DateScheduleChannelRelationInterface::DATE_SCHEDULE_ID
            ],
            $data
        );
    }
}
