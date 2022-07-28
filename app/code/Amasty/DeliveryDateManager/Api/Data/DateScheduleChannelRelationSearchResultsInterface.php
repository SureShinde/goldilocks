<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Api\Data;

/**
 * Search results of getList method of DateScheduleChannelRelation
 */
interface DateScheduleChannelRelationSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get source items list
     *
     * @return \Amasty\DeliveryDateManager\Api\Data\DateScheduleChannelRelationInterface[]
     */
    public function getItems();

    /**
     * Set source items list
     *
     * @param \Amasty\DeliveryDateManager\Api\Data\DateScheduleChannelRelationInterface[] $items
     * @return void
     */
    public function setItems(array $items);

    /**
     * @return int[]
     */
    public function getDateScheduleIds(): array;
}
