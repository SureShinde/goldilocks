<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\TimeInterval;

use Amasty\DeliveryDateManager\Api\Data\TimeIntervalSearchResultsInterface;
use Magento\Framework\Api\SearchResults as AbstractSearchResults;

/**
 * @method \Amasty\DeliveryDateManager\Api\Data\TimeIntervalInterface[] getItems()
 */
class SearchResults extends AbstractSearchResults implements TimeIntervalSearchResultsInterface
{
}
