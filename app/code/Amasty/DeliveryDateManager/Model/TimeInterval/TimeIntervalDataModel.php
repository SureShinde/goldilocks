<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\TimeInterval;

use Amasty\DeliveryDateManager\Api\Data\ChannelSetResultsInterface;
use Amasty\DeliveryDateManager\Api\Data\TimeIntervalInterface;
use Amasty\DeliveryDateManager\Model\AbstractTypifiedModel;

/**
 * @method \Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval getResource()
 * @method \Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval\Collection getCollection()
 */
class TimeIntervalDataModel extends AbstractTypifiedModel implements TimeIntervalInterface
{
    public const CACHE_TAG = 'amdeliv_time';

    /**
     * @var string[]
     */
    protected $_cacheTag = [self::CACHE_TAG, ChannelSetResultsInterface::CACHE_TAG];

    /**
     * @var string
     */
    protected $_eventPrefix = 'amasty_deliverydate_timeinterval';

    protected function _construct()
    {
        $this->_init(\Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval::class);
    }

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->_getData(TimeIntervalInterface::LABEL);
    }

    /**
     * @param string $label
     *
     * @return void
     */
    public function setLabel(string $label): void
    {
        $this->setData(TimeIntervalInterface::LABEL, $label);
    }

    /**
     * @return int
     */
    public function getIntervalId(): int
    {
        return (int)$this->_getData(TimeIntervalInterface::INTERVAL_ID);
    }

    /**
     * @param int|null $intervalId
     *
     * @return void
     */
    public function setIntervalId(?int $intervalId): void
    {
        $this->setData(TimeIntervalInterface::INTERVAL_ID, $intervalId);
    }

    /**
     * @return int|null
     */
    public function getLimitId(): ?int
    {
        $data = $this->_getData(TimeIntervalInterface::LIMIT_ID);
        if ($data === null) {
            return null;
        }

        return (int)$data;
    }

    /**
     * @param int|null $limitId
     *
     * @return void
     */
    public function setLimitId(?int $limitId): void
    {
        $this->setData(TimeIntervalInterface::LIMIT_ID, $limitId);
    }

    /**
     * @return int
     */
    public function getFrom(): int
    {
        return (int)$this->_getData(TimeIntervalInterface::FROM);
    }

    /**
     * @param int $from
     *
     * @return void
     */
    public function setFrom(int $from): void
    {
        $this->setData(TimeIntervalInterface::FROM, $from);
    }

    /**
     * @return int
     */
    public function getTo(): int
    {
        return (int)$this->_getData(TimeIntervalInterface::TO);
    }

    /**
     * @param int $to
     *
     * @return void
     */
    public function setTo(int $to): void
    {
        $this->setData(TimeIntervalInterface::TO, $to);
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return (int)$this->_getData(TimeIntervalInterface::POSITION);
    }

    /**
     * @param int $position
     *
     * @return void
     */
    public function setPosition(int $position): void
    {
        $this->setData(TimeIntervalInterface::POSITION, $position);
    }
}
