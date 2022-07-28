<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\Relations\TimeIntervalDateScheduleRelation;

use Amasty\DeliveryDateManager\Api\Data\TimeIntervalDateScheduleRelationSearchResultsInterface;
use Magento\Framework\Api\SearchResults as AbstractSearchResults;

/**
 * @method \Amasty\DeliveryDateManager\Api\Data\TimeIntervalDateScheduleRelationInterface[] getItems()
 */
class SearchResults extends AbstractSearchResults implements TimeIntervalDateScheduleRelationSearchResultsInterface
{
    /**
     * @return int[]
     */
    public function getTimeIntervalIds(): array
    {
        $scheduleIds = [];
        foreach ($this->getItems() as $link) {
            $scheduleIds[] = $link->getTimeIntervalId();
        }

        return $scheduleIds;
    }
}
