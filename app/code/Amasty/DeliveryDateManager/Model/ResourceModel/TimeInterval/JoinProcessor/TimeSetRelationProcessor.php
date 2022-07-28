<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval\JoinProcessor;

use Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval\Collection;
use Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval\Set;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\JoinProcessor\CustomJoinInterface;
use Magento\Framework\Data\Collection\AbstractDb;

class TimeSetRelationProcessor implements CustomJoinInterface
{
    /**
     * @param AbstractDb|Collection $collection
     * @return true
     */
    public function apply(AbstractDb $collection): bool
    {
        $collection->getSelect()->joinLeft(
            ['set_rel' => $collection->getTable(Set::TIME_SET_RELATION_TABLE)],
            'main_table.interval_id = set_rel.relation_id AND set_rel.relation_type = '
            . Set::RELATION_TYPE_TIME,
            []
        );

        $collection->getSelect()->group('main_table.interval_id');

        return true;
    }
}
