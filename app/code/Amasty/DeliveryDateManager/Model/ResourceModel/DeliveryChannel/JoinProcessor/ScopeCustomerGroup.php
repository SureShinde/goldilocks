<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel\JoinProcessor;

use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel as DeliveryChannelResource;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\JoinProcessor\CustomJoinInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel\Collection;

class ScopeCustomerGroup implements CustomJoinInterface
{
    /**
     * @param AbstractDb|Collection $collection
     * @return bool
     */
    public function apply(AbstractDb $collection): bool
    {
        $collection->getSelect()->joinLeft(
            ['scope_group' => $collection->getTable(DeliveryChannelResource::SCOPE_CUSTOMER_GROUP_TABLE)],
            'main_table.channel_id = scope_group.channel_id',
            []
        );

        return true;
    }
}
