<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Api;

/**
 * Interface for save all kind of relations
 */
interface SaveRelationInterface
{
    /**
     * @param array $firstRelationIds
     * @param array $secondRelationIds
     */
    public function save(array $firstRelationIds, array $secondRelationIds): void;
}
