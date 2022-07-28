<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\Relations\TimeIntervalChannelRelation;

use Amasty\DeliveryDateManager\Api\Data\ChannelSetResultsInterface;
use Amasty\DeliveryDateManager\Api\Data\TimeIntervalChannelRelationInterface;
use Amasty\DeliveryDateManager\Model\AbstractTypifiedModel;

/**
 * @method \Amasty\DeliveryDateManager\Model\ResourceModel\TimeIntervalChannelRelation getResource()
 * @method \Amasty\DeliveryDateManager\Model\ResourceModel\TimeIntervalChannelRelation\Collection getCollection()
 */
class TimeIntervalChannelData extends AbstractTypifiedModel implements TimeIntervalChannelRelationInterface
{
    public const CACHE_TAG = 'amdeliv_time-ch';

    /**
     * @var string[]
     */
    protected $_cacheTag = [self::CACHE_TAG, ChannelSetResultsInterface::CACHE_TAG];

    protected function _construct()
    {
        $this->_init(\Amasty\DeliveryDateManager\Model\ResourceModel\TimeIntervalChannelRelation::class);
    }

    /**
     * @return int
     */
    public function getRelationId(): int
    {
        return (int)$this->_getData(TimeIntervalChannelRelationInterface::RELATION_ID);
    }

    /**
     * @param int $relationId
     *
     * @return void
     */
    public function setRelationId(int $relationId): void
    {
        $this->setData(TimeIntervalChannelRelationInterface::RELATION_ID, $relationId);
    }

    /**
     * @return int
     */
    public function getDeliveryChannelId(): int
    {
        return (int)$this->_getData(TimeIntervalChannelRelationInterface::DELIVERY_CHANNEL_ID);
    }

    /**
     * @param int $deliveryChannelId
     *
     * @return void
     */
    public function setDeliveryChannelId(int $deliveryChannelId): void
    {
        $this->setData(TimeIntervalChannelRelationInterface::DELIVERY_CHANNEL_ID, $deliveryChannelId);
    }

    /**
     * @return int
     */
    public function getTimeIntervalId(): int
    {
        return (int)$this->_getData(TimeIntervalChannelRelationInterface::TIME_INTERVAL_ID);
    }

    /**
     * @param int $timeIntervalId
     *
     * @return void
     */
    public function setTimeIntervalId(int $timeIntervalId): void
    {
        $this->setData(TimeIntervalChannelRelationInterface::TIME_INTERVAL_ID, $timeIntervalId);
    }
}
