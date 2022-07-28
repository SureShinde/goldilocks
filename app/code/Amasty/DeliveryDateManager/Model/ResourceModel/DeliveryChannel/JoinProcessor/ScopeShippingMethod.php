<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel\JoinProcessor;

use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel as DeliveryChannelResource;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\JoinProcessor\CustomJoinInterface;
use Magento\Framework\Data\Collection\AbstractDb;

class ScopeShippingMethod implements CustomJoinInterface
{
    /**
     * @param \Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel\Collection $collection
     * @return true
     */
    public function apply(AbstractDb $collection): bool
    {
        $collection->getSelect()->joinLeft(
            ['scope_shpm' => $collection->getTable(DeliveryChannelResource::SCOPE_SHIPPING_METHOD_TABLE)],
            'main_table.channel_id = scope_shpm.channel_id',
            []
        );

        return true;
    }
}
