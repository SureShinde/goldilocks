<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval\JoinProcessor;

use Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval as TimeIntervalResource;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\JoinProcessor\CustomJoinInterface;
use Magento\Framework\Data\Collection\AbstractDb;

class ChannelProcessor implements CustomJoinInterface
{
    /**
     * @param \Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval\Collection $collection
     * @return true
     */
    public function apply(AbstractDb $collection): bool
    {
        $collection->getSelect()->joinLeft(
            ['channel' => $collection->getTable(TimeIntervalResource::CHANNEL_RELATION_TABLE)],
            'main_table.interval_id = channel.time_interval_id',
            []
        );

        $collection->getSelect()->group('main_table.interval_id');

        return true;
    }
}
