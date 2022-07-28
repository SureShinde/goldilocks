<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DateSchedule;

use Amasty\DeliveryDateManager\Api\Data\DateScheduleSearchResultsInterface;
use Magento\Framework\Api\SearchResults as AbstractSearchResults;

/**
 * @method \Amasty\DeliveryDateManager\Api\Data\DateScheduleInterface[] getItems()
 */
class SearchResults extends AbstractSearchResults implements DateScheduleSearchResultsInterface
{
}
