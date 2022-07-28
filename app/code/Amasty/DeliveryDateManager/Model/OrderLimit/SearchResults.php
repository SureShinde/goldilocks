<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\OrderLimit;

use Amasty\DeliveryDateManager\Api\Data\OrderLimitSearchResultInterface;
use Magento\Framework\Api\SearchResults as AbstractSearchResults;

class SearchResults extends AbstractSearchResults implements OrderLimitSearchResultInterface
{
}
