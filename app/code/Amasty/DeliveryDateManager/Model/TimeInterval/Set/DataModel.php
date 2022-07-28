<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\TimeInterval\Set;

use Amasty\DeliveryDateManager\Model\AbstractTypifiedModel;

/*
 * Set Of Time Intervals it isn't real entity.
 * Store relations of time interval with date schedule and delivery channel.
 * Doesn't used in algorithms. Algorithms should use direct relations.
 * Designed for simplify admin UI.
 */
class DataModel extends AbstractTypifiedModel
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'amasty_deliverydate_time_intervals_set';

    protected function _construct()
    {
        $this->_init(\Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval\Set::class);
    }

    public const KEY_CHANNEL_IDS = 'channel_ids';
    public const KEY_SCHEDULE_IDS = 'schedule_ids';
    public const KEY_TIME_IDS = 'time_ids';

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        $id = parent::getId();
        if ($id === null) {
            return null;
        }

        return (int)$id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return (string)$this->getDataByKey('name');
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->setData('name', $name);
    }

    /**
     * @return int[]
     */
    public function getChannelIds(): array
    {
        return (array)$this->getDataByPath(self::KEY_CHANNEL_IDS);
    }

    /**
     * @param int[] $ids
     */
    public function setChannelIds(array $ids): void
    {
        $this->setData(self::KEY_CHANNEL_IDS, $ids);
    }

    /**
     * @return int[]
     */
    public function getScheduleIds(): array
    {
        return (array)$this->getDataByPath(self::KEY_SCHEDULE_IDS);
    }

    /**
     * @param int[] $ids
     */
    public function setScheduleIds(array $ids): void
    {
        $this->setData(self::KEY_SCHEDULE_IDS, $ids);
    }

    /**
     * @return int[]
     */
    public function getTimeIds(): array
    {
        return (array)$this->getDataByPath(self::KEY_TIME_IDS);
    }

    /**
     * @param int[] $ids
     */
    public function setTimeIds(array $ids): void
    {
        $this->setData(self::KEY_TIME_IDS, $ids);
    }
}
