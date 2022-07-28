<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\Relations\TimeIntervalChannelRelation;

use Amasty\DeliveryDateManager\Api\SaveRelationInterface;
use Amasty\DeliveryDateManager\Model\ResourceModel\TimeIntervalChannelRelation;
use Amasty\DeliveryDateManager\Model\Relations\CollectInsertArray;

/**
 * Date Channel can be related only with 1 or less of Time Interval Set.
 */
class SaveSetOfChannelTime implements SaveRelationInterface
{
    /**
     * @var RelationProvider
     */
    private $relationProvider;

    /**
     * @var TimeIntervalChannelData
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
        TimeIntervalChannelRelation $resourceModel,
        Delete $delete,
        CollectInsertArray $collectInsertArray
    ) {
        $this->relationProvider = $relationProvider;
        $this->resourceModel = $resourceModel;
        $this->delete = $delete;
        $this->collectInsertArray = $collectInsertArray;
    }

    /**
     * @param int[] $channelIds
     * @param int[] $timeIntervalIds
     */
    public function save(array $channelIds, array $timeIntervalIds): void
    {
        $existTimeIds = $this->processOldData($channelIds, $timeIntervalIds);

        $insertArray = $this->collectInsertArray->collect($channelIds, $timeIntervalIds, $existTimeIds);

        if (!empty($insertArray)) {
            $this->resourceModel->insertArray($insertArray);
        }
    }

    /**
     * @param array $channelIds
     * @param array $timeIntervalIds
     *
     * @return array
     */
    private function processOldData(array $channelIds, array $timeIntervalIds): array
    {
        $searchResult = $this->relationProvider->getListByTimeIds($timeIntervalIds);
        $existTimeIds = [];

        foreach ($searchResult->getItems() as $relation) {
            if (!in_array($relation->getDeliveryChannelId(), $channelIds)) {
                $this->delete->deleteByRelation($relation);
            } else {
                $existTimeIds[$relation->getDeliveryChannelId()][] = $relation->getTimeIntervalId();
            }
        }

        return $existTimeIds;
    }
}
