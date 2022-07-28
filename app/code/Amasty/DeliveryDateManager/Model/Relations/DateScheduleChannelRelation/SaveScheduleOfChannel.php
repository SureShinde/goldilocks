<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\Relations\DateScheduleChannelRelation;

use Amasty\DeliveryDateManager\Api\SaveRelationInterface;
use Amasty\DeliveryDateManager\Model\Relations\CollectInsertArray;
use Amasty\DeliveryDateManager\Model\ResourceModel\DateScheduleChannelRelation;

class SaveScheduleOfChannel implements SaveRelationInterface
{
    /**
     * @var RelationProvider
     */
    private $relationProvider;

    /**
     * @var DateScheduleChannelRelation
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
        DateScheduleChannelRelation $resourceModel,
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
     * @param int[] $scheduleIds
     */
    public function save(array $channelIds, array $scheduleIds): void
    {
        $existScheduleIds = $this->deleteOldData($channelIds, $scheduleIds);

        $insertArray = $this->collectInsertArray->collect($channelIds, $scheduleIds, $existScheduleIds);

        if (!empty($insertArray)) {
            $this->resourceModel->insertArray($insertArray);
        }
    }

    /**
     * @param array $channelIds
     * @param array $scheduleIds
     *
     * @return array
     */
    private function deleteOldData(array $channelIds, array $scheduleIds): array
    {
        $searchResult = $this->relationProvider->getListByChannelIds($channelIds);
        $existScheduleIds = [];

        foreach ($searchResult->getItems() as $relation) {
            if (!in_array($relation->getDateScheduleId(), $scheduleIds)) {
                $this->delete->deleteByRelation($relation);
            } else {
                $existScheduleIds[$relation->getDeliveryChannelId()][] = $relation->getDateScheduleId();
            }
        }

        return $existScheduleIds;
    }
}
