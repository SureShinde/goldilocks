<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval;

use Amasty\DeliveryDateManager\Model\TimeInterval\Set\DataModel;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Set extends AbstractDb
{
    /**
     * Tables name constant
     */
    public const MAIN_TABLE = 'amasty_deliverydate_time_intervals_set';
    public const TIME_SET_RELATION_TABLE = 'amasty_deliverydate_time_intervals_set_relation';

    /**
     * Relation types constants
     */
    public const RELATION_TYPE_CHANNEL = 0;
    public const RELATION_TYPE_SCHEDULE = 1;
    public const RELATION_TYPE_TIME = 2;

    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, 'id');
    }

    public function getRelationTable(): string
    {
        return $this->getTable(self::TIME_SET_RELATION_TABLE);
    }

    /**
     * Type id to data model key map.
     *
     * @return array
     */
    public function relationTypesMap()
    {
        return [
            self::RELATION_TYPE_CHANNEL => DataModel::KEY_CHANNEL_IDS,
            self::RELATION_TYPE_SCHEDULE => DataModel::KEY_SCHEDULE_IDS,
            self::RELATION_TYPE_TIME => DataModel::KEY_TIME_IDS,
        ];
    }

    /**
     * @param int $relationType
     * @param int[] $relationIds
     *
     * @return array array(<relation_id> => <set_id>, ...)
     */
    public function loadSetIdsByRelationIds(int $relationType, array $relationIds): array
    {
        $connection = $this->getConnection();
        $relation = $connection->select()
            ->from(
                $this->getRelationTable(),
                ['relation_id', 'set_id']
            )->where('relation_type = :relationType')
            ->where('relation_id IN (?)', $relationIds);

        return $connection->fetchPairs($relation, ['relationType' => $relationType]);
    }

    /**
     * @param int $setId
     *
     * @return array array(<relation_type> => array(<relation_id>, ...), ...)
     */
    public function loadRelationIdsForSetId(int $setId): array
    {
        $connection = $this->getConnection();
        $relation = $connection->select()
            ->from(
                $this->getRelationTable(),
                ['relation_type', 'relation_id']
            )->where('set_id = :set_id');

        return $connection->fetchAll($relation, ['set_id' => $setId], \Zend_Db::FETCH_GROUP|\Zend_Db::FETCH_COLUMN);
    }

    /**
     * @param DataModel $object
     * @return void
     */
    public function setRelationsToSet(DataModel $object): void
    {
        $setId = (int)$object->getId();
        if ($setId) {
            $relationKeyMap = $this->relationTypesMap();
            foreach ($this->loadRelationIdsForSetId($setId) as $relationType => $relationIds) {
                $object->setData($relationKeyMap[$relationType], $relationIds);
            }
        }
    }

    /**
     * Perform actions after entity load
     *
     * @param DataModel|AbstractModel $object
     * @return void
     */
    public function _afterLoad(AbstractModel $object): void
    {
        parent::_afterLoad($object);

        $this->setRelationsToSet($object);
    }

    /**
     * Perform actions after object save
     *
     * @param DataModel|AbstractModel $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        $deleteWhere = [];
        $insert = [];
        $setId = $object->getId();

        foreach ($this->relationTypesMap() as $typeId => $dataKey) {
            if ($object->dataHasChangedFor($dataKey)) {
                if ($typeId === self::RELATION_TYPE_SCHEDULE) {
                    $this->deleteSchedulesRelation((array)$object->getDataByKey($dataKey));
                }

                $deleteWhere[] = 'relation_type = ' . $typeId;
                foreach ($object->getDataByKey($dataKey) as $relationId) {
                    $insert[] = [$setId, $typeId, $relationId];
                }
            }
        }

        $this->processDeleteRelations($object, $deleteWhere);
        if (!empty($insert)) {
            $this->getConnection()->insertArray(
                $this->getRelationTable(),
                ['set_id', 'relation_type', 'relation_id'],
                $insert
            );
        }

        return $this;
    }

    /**
     * @param DataModel|AbstractModel $object
     * @param array $deleteWhere
     */
    private function processDeleteRelations(AbstractModel $object, array $deleteWhere): void
    {
        if (!$object->isObjectNew() && !empty($deleteWhere)) {
            $where = 'set_id = ' . $object->getId() . ' ' . Select::SQL_AND . '(('
                . implode(') ' . Select::SQL_OR . ' (', $deleteWhere) . '))';
            $this->getConnection()->delete($this->getRelationTable(), $where);
        }
    }

    /**
     * Delete schedules relation for all time sets
     * It's necessary to keep 1:1 relation between time set and schedule and remove relations for deleted schedules
     *
     * @see 'amasty_deliverydate_dateschedule_delete_after' event
     * @param array $scheduleIds
     */
    public function deleteSchedulesRelation(array $scheduleIds): void
    {
        if (!empty($scheduleIds)) {
            $this->getConnection()->delete(
                $this->getRelationTable(),
                'relation_type = ' . self::RELATION_TYPE_SCHEDULE
                . ' AND relation_id IN (' . implode(',', $scheduleIds) . ')'
            );
        }
    }
}
