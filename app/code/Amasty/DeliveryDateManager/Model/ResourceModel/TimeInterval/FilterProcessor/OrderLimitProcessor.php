<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval\FilterProcessor;

use Amasty\DeliveryDateManager\Model\ResourceModel\OrderLimit;
use Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval\Collection;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor\CustomFilterInterface;
use Magento\Framework\Data\Collection\AbstractDb;

class OrderLimitProcessor implements CustomFilterInterface
{
    /**
     * @param Filter $filter
     * @param AbstractDb $collection
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Filter $filter, AbstractDb $collection): bool
    {
        $collection->getSelect()->joinLeft(
            ['limit' => $collection->getTable(OrderLimit::MAIN_TABLE)],
            'main_table.limit_id = limit.limit_id',
            []
        )->where(
            'limit.interval_limit is NULL OR limit.interval_limit > 0'
        );

        return true;
    }
}
