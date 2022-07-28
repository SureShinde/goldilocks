<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\Relations\TimeIntervalChannelRelation;

use Amasty\DeliveryDateManager\Api\Data\TimeIntervalChannelRelationSearchResultsInterface;
use Magento\Framework\Api\SearchResults as AbstractSearchResults;

/**
 * @method \Amasty\DeliveryDateManager\Api\Data\TimeIntervalChannelRelationInterface[] getItems()
 */
class SearchResults extends AbstractSearchResults implements TimeIntervalChannelRelationSearchResultsInterface
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
