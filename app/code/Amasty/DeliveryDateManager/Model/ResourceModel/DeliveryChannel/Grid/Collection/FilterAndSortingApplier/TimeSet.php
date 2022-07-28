<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel\Grid\Collection\FilterAndSortingApplier;

use Amasty\DeliveryDateManager\Api\Data\DateScheduleChannelRelationInterface;
use Amasty\DeliveryDateManager\Api\Data\DeliveryChannelInterface;
use Amasty\DeliveryDateManager\Api\Data\TimeIntervalInterface;
use Amasty\DeliveryDateManager\Model\ResourceModel\Collection\FilterAndSortingApplierInterface;
use Amasty\DeliveryDateManager\Model\ResourceModel\DateScheduleChannelRelation;
use Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval as TimeIntervalResource;
use Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval\Set;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class TimeSet implements FilterAndSortingApplierInterface
{
    public const FLAG_NAME = 'time_sets';
    public const SET_REL_ALIAS = 'set_rel';
    public const CHANNEL_SCHEDULE_REL_ALIAS = 'cs_rel';
    public const INTERVAL_REL_ALIAS = 'int_rel';
    public const INTERVAL_ALIAS = 'interval';
    public const TIME_SET_ALIAS = 'time_set';
    public const SET_ID = 'id';

    /**
     * @var array
     */
    private $fieldsMap = [
        self::TIME_SET_ALIAS . '_' . self::SET_ID => self::TIME_SET_ALIAS . '.' . self::SET_ID,
        TimeIntervalInterface::INTERVAL_ID => self::INTERVAL_ALIAS . '.' . TimeIntervalInterface::INTERVAL_ID,
        self::INTERVAL_ALIAS . '_' . TimeIntervalInterface::FROM =>
            self::INTERVAL_ALIAS . '.' . TimeIntervalInterface::FROM,
        self::INTERVAL_ALIAS . '_' . TimeIntervalInterface::TO =>
            self::INTERVAL_ALIAS . '.' . TimeIntervalInterface::TO
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

        $this->joinTimeSetsTable($collection);
        if ($field !== self::TIME_SET_ALIAS . '_' . self::SET_ID) {
            $this->joinTimeIntervalsTable($collection);
        }

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

        $this->joinTimeSetsTable($collection);

        if ($field !== self::TIME_SET_ALIAS . '_' . self::SET_ID) {
            $this->joinTimeIntervalsTable($collection);
        }

        $collection->getSelect()->order($mappedField . ' '. $direction);
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
    private function joinTimeSetsTable(AbstractCollection $collection): void
    {
        if (!$collection->getFlag(self::FLAG_NAME . '_' .  self::TIME_SET_ALIAS)) {
            $collection->getSelect()
                ->joinLeft(
                    [
                        self::CHANNEL_SCHEDULE_REL_ALIAS =>
                            $collection->getTable(DateScheduleChannelRelation::MAIN_TABLE)
                    ],
                    'cs_rel.delivery_channel_id = main_table.channel_id',
                    []
                )->joinLeft(
                    [self::SET_REL_ALIAS => $collection->getTable(Set::TIME_SET_RELATION_TABLE)],
                    'set_rel.relation_id = cs_rel.date_schedule_id AND '
                    . 'set_rel.relation_type = ' . Set::RELATION_TYPE_SCHEDULE,
                    []
                )->joinLeft(
                    [self::TIME_SET_ALIAS => $collection->getTable(Set::MAIN_TABLE)],
                    'set_rel.set_id = time_set.id',
                    []
                )->group('main_table.' . DeliveryChannelInterface::CHANNEL_ID);

            $collection->setFlag(self::FLAG_NAME . '_' .  self::TIME_SET_ALIAS, true);
        }
    }

    /**
     * @param AbstractCollection $collection
     * @return void
     */
    private function joinTimeIntervalsTable(AbstractCollection $collection): void
    {
        if (!$collection->getFlag(self::FLAG_NAME . '_' .  self::INTERVAL_ALIAS)) {
            $collection->getSelect()
                ->joinLeft(
                    [self::INTERVAL_REL_ALIAS => $collection->getTable(Set::TIME_SET_RELATION_TABLE)],
                    'int_rel.set_id = time_set.id AND '
                    . 'int_rel.relation_type = ' . Set::RELATION_TYPE_TIME,
                    []
                )->joinLeft(
                    [self::INTERVAL_ALIAS => $collection->getTable(TimeIntervalResource::MAIN_TABLE)],
                    'int_rel.relation_id = interval.interval_id',
                    []
                );

            $collection->setFlag(self::FLAG_NAME . '_' .  self::INTERVAL_ALIAS, true);
        }
    }
}
