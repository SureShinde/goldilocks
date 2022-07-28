<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\Relations\TimeIntervalDateScheduleRelation;

use Amasty\DeliveryDateManager\Api\Data\ChannelSetResultsInterface;
use Amasty\DeliveryDateManager\Api\Data\TimeIntervalDateScheduleRelationInterface;
use Amasty\DeliveryDateManager\Model\AbstractTypifiedModel;

/**
 * @method \Amasty\DeliveryDateManager\Model\ResourceModel\TimeIntervalDateScheduleRelation getResource()
 * @method \Amasty\DeliveryDateManager\Model\ResourceModel\TimeIntervalDateScheduleRelation\Collection getCollection()
 */
class TimeIntervalDateScheduleData extends AbstractTypifiedModel implements TimeIntervalDateScheduleRelationInterface
{
    public const CACHE_TAG = 'amdeliv_time-sch';

    /**
     * @var string[]
     */
    protected $_cacheTag = [self::CACHE_TAG, ChannelSetResultsInterface::CACHE_TAG];

    protected function _construct()
    {
        $this->_init(\Amasty\DeliveryDateManager\Model\ResourceModel\TimeIntervalDateScheduleRelation::class);
    }

    /**
     * @return int
     */
    public function getRelationId(): int
    {
        return (int)$this->_getData(TimeIntervalDateScheduleRelationInterface::RELATION_ID);
    }

    /**
     * @param int $relationId
     *
     * @return void
     */
    public function setRelationId(int $relationId): void
    {
        $this->setData(TimeIntervalDateScheduleRelationInterface::RELATION_ID, $relationId);
    }

    /**
     * @return int
     */
    public function getDateScheduleId(): int
    {
        return (int)$this->_getData(TimeIntervalDateScheduleRelationInterface::DATE_SCHEDULE_ID);
    }

    /**
     * @param int $dateScheduleId
     *
     * @return void
     */
    public function setDateScheduleId(int $dateScheduleId): void
    {
        $this->setData(TimeIntervalDateScheduleRelationInterface::DATE_SCHEDULE_ID, $dateScheduleId);
    }

    /**
     * @return int
     */
    public function getTimeIntervalId(): int
    {
        return (int)$this->_getData(TimeIntervalDateScheduleRelationInterface::TIME_INTERVAL_ID);
    }

    /**
     * @param int $timeIntervalId
     *
     * @return void
     */
    public function setTimeIntervalId(int $timeIntervalId): void
    {
        $this->setData(TimeIntervalDateScheduleRelationInterface::TIME_INTERVAL_ID, $timeIntervalId);
    }
}
