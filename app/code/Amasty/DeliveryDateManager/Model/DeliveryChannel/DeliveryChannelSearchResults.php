<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryChannel;

use Amasty\DeliveryDateManager\Api\Data\DeliveryChannelSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * @method \Amasty\DeliveryDateManager\Api\Data\DeliveryChannelInterface[] getItems()
 */
class DeliveryChannelSearchResults extends SearchResults implements DeliveryChannelSearchResultsInterface
{
    /**
     * @return int[]
     */
    public function getIds(): array
    {
        $ids = [];
        foreach ($this->getItems() as $item) {
            $ids[] = $item->getChannelId();
        }

        return $ids;
    }
}
