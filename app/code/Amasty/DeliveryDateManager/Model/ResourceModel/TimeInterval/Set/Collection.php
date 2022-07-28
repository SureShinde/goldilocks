<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval\Set;

use Amasty\DeliveryDateManager\Model\ResourceModel\AbstractTypifiedCollection;
use Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval\Set as ResourceModel;
use Amasty\DeliveryDateManager\Model\TimeInterval\Set\DataModel;
use Magento\Framework\DB\Select;

/**
 * @method DataModel[] getItems()
 * @method ResourceModel getResource()
 */
class Collection extends AbstractTypifiedCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * @var array
     */
    private $relationFilters = [];

    protected function _construct()
    {
        $this->_init(
            DataModel::class,
            ResourceModel::class
        );
    }

    /**
     * @param int $channelId
     *
     * @return $this
     */
    public function addChannelIdFilter(int $channelId): Collection
    {
        $this->relationFilters[] = 'srel.relation_type=' . ResourceModel::RELATION_TYPE_CHANNEL
            . ' AND srel.relation_id=' . $channelId;

        return $this;
    }

    /**
     * @param int[] $scheduleIds
     *
     * @return $this
     */
    public function addScheduleIdsFilter(array $scheduleIds): Collection
    {
        $this->relationFilters[] = 'srel.relation_type=' . ResourceModel::RELATION_TYPE_SCHEDULE
            . ' AND srel.relation_id IN (' . implode(', ', $scheduleIds) . ')';

        return $this;
    }

    /**
     * @param int[] $timeIntervalIds
     *
     * @return $this
     */
    public function addTimeIdsFilter(array $timeIntervalIds): Collection
    {
        $this->relationFilters[] = 'srel.relation_type=' . ResourceModel::RELATION_TYPE_TIME
            . ' AND srel.relation_id IN (' . implode(', ', $timeIntervalIds) . ')';

        return $this;
    }

    /**
     * Join relations table for filter
     */
    public function joinRelations(): void
    {
        $select = $this->getSelect();
        $fromPart = $select->getPart(Select::FROM);

        if (!isset($fromPart['srel'])) {
            $select->joinLeft(
                ['srel' => $this->getTable(ResourceModel::TIME_SET_RELATION_TABLE)],
                'main_table.id=srel.set_id',
                []
            );
        }
    }

    /**
     * Hook for operations before rendering filters
     *
     * @return void
     */
    protected function _renderFiltersBefore(): void
    {
        if (!empty($this->relationFilters)) {
            $this->joinRelations();

            $where = '(' . implode(') ' . \Magento\Framework\DB\Select::SQL_OR . ' (', $this->relationFilters) . ')';
            $this->getSelect()->where($where);
            $this->relationFilters = [];
        }
    }
}
