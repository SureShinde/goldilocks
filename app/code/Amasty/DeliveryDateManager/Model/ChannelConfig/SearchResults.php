<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ChannelConfig;

use Amasty\DeliveryDateManager\Api\Data\ChannelConfigSearchResultInterface;
use Magento\Framework\Api\SearchResults as AbstractSearchResults;

class SearchResults extends AbstractSearchResults implements ChannelConfigSearchResultInterface
{
}
