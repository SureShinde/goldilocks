<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DateSchedule;

use Amasty\DeliveryDateManager\Api\Data\DateScheduleInterface;
use Amasty\DeliveryDateManager\Api\Data\DateScheduleSearchResultsInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\Search\SearchCriteriaFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;

class Provider
{
    /**
     * @var GetList
     */
    private $getList;

    /**
     * @var SearchCriteriaFactory
     */
    private $searchCriteriaFactory;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var FilterGroupBuilder
     */
    private $filterGroupBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    public function __construct(
        GetList $getList,
        SearchCriteriaFactory $searchCriteriaFactory,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        SortOrderBuilder $sortOrderBuilder
    ) {
        $this->getList = $getList;
        $this->searchCriteriaFactory = $searchCriteriaFactory;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
    }

    /**
     * @param int[] $ids
     *
     * @return DateScheduleSearchResultsInterface
     */
    public function getScheduleByIds(array $ids): DateScheduleSearchResultsInterface
    {
        $searchCriteria = $this->searchCriteriaFactory->create();
        $searchCriteria->setFilterGroups([$this->createFilters($ids)]);

        $sorting = [
            $this->createSorOrder(DateScheduleInterface::IS_AVAILABLE),
            $this->createSorOrder(DateScheduleInterface::TYPE)
        ];

        $searchCriteria->setSortOrders($sorting);

        return $this->getList->execute($searchCriteria);
    }

    /**
     * @param string $field
     *
     * @return SortOrder
     */
    private function createSorOrder(string $field): SortOrder
    {
        $this->sortOrderBuilder->setField($field);
        $this->sortOrderBuilder->setAscendingDirection();

        return $this->sortOrderBuilder->create();
    }

    /**
     * @param array $ids
     *
     * @return FilterGroup
     */
    private function createFilters(array $ids): FilterGroup
    {
        $this->filterBuilder->setField(DateScheduleInterface::SCHEDULE_ID);
        $this->filterBuilder->setConditionType('in');
        $this->filterBuilder->setValue($ids);
        $this->filterGroupBuilder->setFilters([$this->filterBuilder->create()]);

        return $this->filterGroupBuilder->create();
    }
}
