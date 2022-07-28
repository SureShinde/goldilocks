<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Api\Data;

/**
 * Search results of getList method of TimeIntervalDateScheduleRelation
 */
interface TimeIntervalDateScheduleRelationSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get source items list
     *
     * @return \Amasty\DeliveryDateManager\Api\Data\TimeIntervalDateScheduleRelationInterface[]
     */
    public function getItems();

    /**
     * Set source items list
     *
     * @param \Amasty\DeliveryDateManager\Api\Data\TimeIntervalDateScheduleRelationInterface[] $items
     * @return void
     */
    public function setItems(array $items);

    /**
     * @return int[]
     */
    public function getTimeIntervalIds(): array;
}
