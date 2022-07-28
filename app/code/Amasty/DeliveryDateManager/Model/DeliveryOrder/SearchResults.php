<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryOrder;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateOrderSearchResultInterface;
use Magento\Framework\Api\SearchResults as AbstractSearchResults;

/**
 * @method \Amasty\DeliveryDateManager\Api\Data\DeliveryDateOrderInterface[] getItems()
 */
class SearchResults extends AbstractSearchResults implements DeliveryDateOrderSearchResultInterface
{
}
