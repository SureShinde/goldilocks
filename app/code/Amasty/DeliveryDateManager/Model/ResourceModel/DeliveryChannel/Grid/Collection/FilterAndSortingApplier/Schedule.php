<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel\Grid\Collection\FilterAndSortingApplier;

use Amasty\DeliveryDateManager\Api\Data\DateScheduleInterface;
use Amasty\DeliveryDateManager\Api\Data\DeliveryChannelInterface;
use Amasty\DeliveryDateManager\Api\Data\OrderLimitInterface;
use Amasty\DeliveryDateManager\Model\ResourceModel\Collection\FilterAndSortingApplierInterface;
use Amasty\DeliveryDateManager\Model\ResourceModel\DateSchedule;
use Amasty\DeliveryDateManager\Model\ResourceModel\DateScheduleChannelRelation;
use Amasty\DeliveryDateManager\Model\ResourceModel\OrderLimit;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Schedule implements FilterAndSortingApplierInterface
{
    public const FLAG_NAME = 'schedules';
    public const CHANNEL_REL_ALIAS = 'channel_rel';
    public const SCHEDULE_ALIAS = 'schedule';
    public const ORDER_LIMIT_ALIAS = 'order_limit';

    /**
     * @var array
     */
    private $fieldsMap = [
        DateScheduleInterface::SCHEDULE_ID => self::SCHEDULE_ALIAS . '.' . DateScheduleInterface::SCHEDULE_ID,
        DateScheduleInterface::IS_AVAILABLE => self::SCHEDULE_ALIAS . '.' . DateScheduleInterface::IS_AVAILABLE,
        OrderLimitInterface::LIMIT_ID => self::ORDER_LIMIT_ALIAS . '.' . OrderLimitInterface::LIMIT_ID,
        OrderLimitInterface::INTERVAL_LIMIT => self::ORDER_LIMIT_ALIAS . '.' . OrderLimitInterface::INTERVAL_LIMIT,
        OrderLimitInterface::DAY_LIMIT => self::ORDER_LIMIT_ALIAS . '.' . OrderLimitInterface::DAY_LIMIT,
        self::SCHEDULE_ALIAS . '_' . DateScheduleInterface::TYPE =>
            self::SCHEDULE_ALIAS . '.' . DateScheduleInterface::TYPE
    ];

    public function __construct(
        array $fieldsMap = []
    ) {
        $this->fieldsMap = array_merge($this->fieldsMap, $fieldsMap);
    }

    /**
     * @param AbstractCollection $collection
     * @param string $field
     * @param array|string|null $condition
     */
    public function applyFilter(AbstractCollection $collection, string $field, $condition = null): void
    {
        $mappedField = $this->fieldsMap[$field];

        $this->joinTable($collection);
        $collection
            ->addFilter($field, $condition, 'public')
            ->addFilterToMap($field, $mappedField);
    }

    /**
     * @param AbstractCollection $collection
     * @param string $field
     * @param string $direction
     */
    public function applySorting(AbstractCollection $collection, string $field, string $direction): void
    {
        $mappedField = $this->fieldsMap[$field];

        $this->joinTable($collection);
        $collection->getSelect()->order($mappedField . ' ' . $direction);
    }

    /**
     * @param string $field
     * @return bool
     */
    public function canApply(string $field): bool
    {
        $applicableFields = array_keys($this->fieldsMap);
        $mappedField = $this->fieldsMap[$field] ?? null;

        return in_array($field, $applicableFields) && $mappedField;
    }

    /**
     * @param AbstractCollection $collection
     * @return void
     */
    private function joinTable(AbstractCollection $collection): void
    {
        if (!$collection->getFlag(self::FLAG_NAME)) {
            $collection->getSelect()
                ->joinLeft(
                    [self::CHANNEL_REL_ALIAS => $collection->getTable(DateScheduleChannelRelation::MAIN_TABLE)],
                    'channel_rel.delivery_channel_id = main_table.channel_id',
                    []
                )->joinLeft(
                    [self::SCHEDULE_ALIAS => $collection->getTable(DateSchedule::MAIN_TABLE)],
                    'channel_rel.date_schedule_id = schedule.schedule_id',
                    []
                )->joinLeft(
                    [self::ORDER_LIMIT_ALIAS => $collection->getTable(OrderLimit::MAIN_TABLE)],
                    'order_limit.limit_id = schedule.limit_id',
                    []
                )->group('main_table.' . DeliveryChannelInterface::CHANNEL_ID);

            $collection->setFlag(self::FLAG_NAME, true);
        }
    }
}
