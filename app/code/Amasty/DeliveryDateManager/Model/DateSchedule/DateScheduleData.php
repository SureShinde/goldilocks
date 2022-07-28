<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DateSchedule;

use Amasty\DeliveryDateManager\Api\Data\ChannelSetResultsInterface;
use Amasty\DeliveryDateManager\Api\Data\DateScheduleInterface;
use Amasty\DeliveryDateManager\Model\AbstractTypifiedModel;

/**
 * @method \Amasty\DeliveryDateManager\Model\ResourceModel\DateSchedule getResource()
 * @method \Amasty\DeliveryDateManager\Model\ResourceModel\DateSchedule\Collection getCollection()
 */
class DateScheduleData extends AbstractTypifiedModel implements DateScheduleInterface
{
    public const CACHE_TAG = 'amdeliv_sch';

    /**
     * @var string[]
     */
    protected $_cacheTag = [self::CACHE_TAG, ChannelSetResultsInterface::CACHE_TAG];

    /**
     * @var string
     */
    protected $_eventPrefix = 'amasty_deliverydate_dateschedule';

    protected function _construct()
    {
        $this->_init(\Amasty\DeliveryDateManager\Model\ResourceModel\DateSchedule::class);
    }

    /**
     * @return int
     */
    public function getScheduleId(): int
    {
        return (int)$this->_getData(DateScheduleInterface::SCHEDULE_ID);
    }

    /**
     * @param int|null $scheduleId
     *
     * @return void
     */
    public function setScheduleId(?int $scheduleId): void
    {
        $this->setData(DateScheduleInterface::SCHEDULE_ID, $scheduleId);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->_getData(DateScheduleInterface::NAME);
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function setName(string $name): void
    {
        $this->setData(DateScheduleInterface::NAME, $name);
    }

    /**
     * @return int|null
     */
    public function getLimitId(): ?int
    {
        $data = $this->_getData(DateScheduleInterface::LIMIT_ID);
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
        $this->setData(DateScheduleInterface::LIMIT_ID, $limitId);
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return (int)$this->_getData(DateScheduleInterface::TYPE);
    }

    /**
     * @param int $type
     *
     * @return void
     */
    public function setType(int $type): void
    {
        $this->setData(DateScheduleInterface::TYPE, $type);
    }

    /**
     * @return string
     */
    public function getFrom(): string
    {
        return $this->_getData(DateScheduleInterface::FROM);
    }

    /**
     * @param string $from
     *
     * @return void
     */
    public function setFrom(string $from): void
    {
        $this->setData(DateScheduleInterface::FROM, $from);
    }

    /**
     * @return string
     */
    public function getTo(): string
    {
        return $this->_getData(DateScheduleInterface::TO);
    }

    /**
     * @param string $to
     *
     * @return void
     */
    public function setTo(string $to): void
    {
        $this->setData(DateScheduleInterface::TO, $to);
    }

    /**
     * @return int
     */
    public function getIsAvailable(): int
    {
        return (int)$this->_getData(DateScheduleInterface::IS_AVAILABLE);
    }

    /**
     * @param int $isAvailable
     *
     * @return void
     */
    public function setIsAvailable(int $isAvailable): void
    {
        $this->setData(DateScheduleInterface::IS_AVAILABLE, $isAvailable);
    }
}
