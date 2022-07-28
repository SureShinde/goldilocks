<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\Relations\DateScheduleChannelRelation;

use Amasty\DeliveryDateManager\Api\Data\DateScheduleChannelRelationSearchResultsInterface;
use Magento\Framework\Api\SearchResults as AbstractSearchResults;

/**
 * @method \Amasty\DeliveryDateManager\Api\Data\DateScheduleChannelRelationInterface[] getItems()
 */
class SearchResults extends AbstractSearchResults implements DateScheduleChannelRelationSearchResultsInterface
{
    /**
     * @return int[]
     */
    public function getDateScheduleIds(): array
    {
        $scheduleIds = [];
        foreach ($this->getItems() as $link) {
            $scheduleIds[] = $link->getDateScheduleId();
        }

        return $scheduleIds;
    }
}
