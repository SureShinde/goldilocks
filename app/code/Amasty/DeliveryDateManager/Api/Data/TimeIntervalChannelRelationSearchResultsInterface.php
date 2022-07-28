<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Api\Data;

/**
 * Search results of getList method of TimeIntervalChannelRelation
 */
interface TimeIntervalChannelRelationSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get source items list
     *
     * @return \Amasty\DeliveryDateManager\Api\Data\TimeIntervalChannelRelationInterface[]
     */
    public function getItems();

    /**
     * Set source items list
     *
     * @param \Amasty\DeliveryDateManager\Api\Data\TimeIntervalChannelRelationInterface[] $items
     * @return void
     */
    public function setItems(array $items);

    /**
     * @return int[]
     */
    public function getTimeIntervalIds(): array;
}
