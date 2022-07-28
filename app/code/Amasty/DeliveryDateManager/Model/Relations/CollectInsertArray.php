<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\Relations;

class CollectInsertArray
{
    /**
     * @param int[] $relationIds
     * @param int[] $entityIds
     * @param int[] $existIds
     *
     * @return array
     */
    public function collect(array $relationIds, array $entityIds, array $existIds): array
    {
        $insertArray = [];
        foreach ($relationIds as $relationId) {
            $existEntities = [];
            if (isset($existIds[$relationId])) {
                $existEntities = &$existIds[$relationId];
            }

            foreach ($entityIds as $entityId) {
                if (!in_array($entityId, $existEntities)) {
                    $insertArray[] = [$relationId, $entityId];
                }
            }
        }

        return $insertArray;
    }
}
