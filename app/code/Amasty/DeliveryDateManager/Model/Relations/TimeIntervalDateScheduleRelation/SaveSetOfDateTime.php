<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\Relations\TimeIntervalDateScheduleRelation;

use Amasty\DeliveryDateManager\Api\SaveRelationInterface;
use Amasty\DeliveryDateManager\Model\Relations\CollectInsertArray;
use Amasty\DeliveryDateManager\Model\ResourceModel\TimeIntervalDateScheduleRelation;

/**
 * Date schedule can be related only with 1 or less of Time Interval Set.
 */
class SaveSetOfDateTime implements SaveRelationInterface
{
    /**
     * @var RelationProvider
     */
    private $relationProvider;

    /**
     * @var TimeIntervalDateScheduleRelation
     */
    private $resourceModel;

    /**
     * @var Delete
     */
    private $delete;

    /**
     * @var CollectInsertArray
     */
    private $collectInsertArray;

    public function __construct(
        RelationProvider $relationProvider,
        TimeIntervalDateScheduleRelation $resourceModel,
        Delete $delete,
        CollectInsertArray $collectInsertArray
    ) {
        $this->relationProvider = $relationProvider;
        $this->resourceModel = $resourceModel;
        $this->delete = $delete;
        $this->collectInsertArray = $collectInsertArray;
    }

    /**
     * @param int[] $dateScheduleIds
     * @param int[] $timeIntervalIds
     */
    public function save(array $dateScheduleIds, array $timeIntervalIds): void
    {
        $existTimeIds = $this->processOldData($dateScheduleIds, $timeIntervalIds);

        $insertArray = $this->collectInsertArray->collect($dateScheduleIds, $timeIntervalIds, $existTimeIds);

        if (!empty($insertArray)) {
            $this->resourceModel->insertArray($insertArray);
        }
    }

    /**
     * @param array $dateScheduleIds
     * @param array $timeIntervalIds
     *
     * @return array
     */
    private function processOldData(array $dateScheduleIds, array $timeIntervalIds): array
    {
        $byScheduleSearchResult = $this->relationProvider->getListByDateScheduleIds($dateScheduleIds);
        $byIntervalsSearchResult = $this->relationProvider->getListByTimeIds($timeIntervalIds);
        $items = $byScheduleSearchResult->getItems() + $byIntervalsSearchResult->getItems();
        $existTimeIds = [];

        foreach ($items as $relation) {
            if (!in_array($relation->getTimeIntervalId(), $timeIntervalIds)
                || !in_array($relation->getDateScheduleId(), $dateScheduleIds)
            ) {
                $this->delete->deleteByRelation($relation);
            } else {
                $existTimeIds[$relation->getDateScheduleId()][] = $relation->getTimeIntervalId();
            }
        }

        return $existTimeIds;
    }
}
