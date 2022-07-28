<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel\DateSchedule;

use Amasty\DeliveryDateManager\Model\ResourceModel\AbstractTypifiedCollection;
use Amasty\DeliveryDateManager\Model\ResourceModel\DateScheduleChannelRelation;

/**
 * @method \Amasty\DeliveryDateManager\Model\DateSchedule\DateScheduleData[] getItems()
 */
class Collection extends AbstractTypifiedCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'schedule_id';

    protected function _construct()
    {
        $this->_init(
            \Amasty\DeliveryDateManager\Model\DateSchedule\DateScheduleData::class,
            \Amasty\DeliveryDateManager\Model\ResourceModel\DateSchedule::class
        );
    }

    /**
     * Add not used in other channels filter
     *
     * @param int|null $channelId
     * @return $this
     */
    public function addNotUsedFilter(?int $channelId = null): self
    {
        $fields = ['channel_rel.date_schedule_id'];
        $conditions[] = ['null' => true];

        $this->getSelect()
            ->joinLeft(
                ['channel_rel' => $this->getTable(DateScheduleChannelRelation::MAIN_TABLE)],
                'main_table.schedule_id = channel_rel.date_schedule_id',
                []
            );

        if ($channelId) {
            $fields[] = 'channel_rel.delivery_channel_id';
            $conditions[] = ['eq' => $channelId];
        }

        $this->addFieldToFilter($fields, $conditions);

        return $this;
    }
}
