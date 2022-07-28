<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\Relations\DateScheduleChannelRelation;

use Amasty\DeliveryDateManager\Api\Data\ChannelSetResultsInterface;
use Amasty\DeliveryDateManager\Api\Data\DateScheduleChannelRelationInterface;
use Amasty\DeliveryDateManager\Model\AbstractTypifiedModel;

/**
 * @method \Amasty\DeliveryDateManager\Model\ResourceModel\DateScheduleChannelRelation getResource()
 * @method \Amasty\DeliveryDateManager\Model\ResourceModel\DateScheduleChannelRelation\Collection getCollection()
 */
class DateScheduleChannelRelationData extends AbstractTypifiedModel implements DateScheduleChannelRelationInterface
{
    public const CACHE_TAG = 'amdeliv_sch-ch';

    /**
     * @var string[]
     */
    protected $_cacheTag = [self::CACHE_TAG, ChannelSetResultsInterface::CACHE_TAG];

    /**
     * @var string
     */
    protected $_eventPrefix = 'amasty_deliverydate_schedulechannelrelation';

    protected function _construct()
    {
        $this->_init(\Amasty\DeliveryDateManager\Model\ResourceModel\DateScheduleChannelRelation::class);
    }

    /**
     * @return int
     */
    public function getRelationId(): int
    {
        return (int)$this->_getData(DateScheduleChannelRelationInterface::RELATION_ID);
    }

    /**
     * @param int $relationId
     *
     * @return void
     */
    public function setRelationId(int $relationId): void
    {
        $this->setData(DateScheduleChannelRelationInterface::RELATION_ID, $relationId);
    }

    /**
     * @return int
     */
    public function getDeliveryChannelId(): int
    {
        return (int)$this->_getData(DateScheduleChannelRelationInterface::DELIVERY_CHANNEL_ID);
    }

    /**
     * @param int $deliveryChannelId
     *
     * @return void
     */
    public function setDeliveryChannelId(int $deliveryChannelId): void
    {
        $this->setData(DateScheduleChannelRelationInterface::DELIVERY_CHANNEL_ID, $deliveryChannelId);
    }

    /**
     * @return int
     */
    public function getDateScheduleId(): int
    {
        return (int)$this->_getData(DateScheduleChannelRelationInterface::DATE_SCHEDULE_ID);
    }

    /**
     * @param int $dateScheduleId
     *
     * @return void
     */
    public function setDateScheduleId(int $dateScheduleId): void
    {
        $this->setData(DateScheduleChannelRelationInterface::DATE_SCHEDULE_ID, $dateScheduleId);
    }
}
