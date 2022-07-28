<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryChannelScope;

use Amasty\DeliveryDateManager\Api\Data\DeliveryChannelInterface;
use Amasty\DeliveryDateManager\Api\DeliveryChannelScopeProcessorInterface;
use Amasty\DeliveryDateManager\Model\DeliveryChannel\Source\Status;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;

class GeneralProcessor implements DeliveryChannelScopeProcessorInterface
{
    /**
     * @var FilterGroupBuilder
     */
    private $filterGroupBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    public function __construct(
        FilterGroupBuilder $filterGroupBuilder,
        FilterBuilder $filterBuilder,
        SortOrderBuilder $sortOrderBuilder
    ) {
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     */
    public function processSearchCriteria(SearchCriteriaInterface $searchCriteria): void
    {
        $this->addFilterGroup($searchCriteria);
        $this->addSortOrder($searchCriteria);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     */
    private function addFilterGroup(SearchCriteriaInterface $searchCriteria): void
    {
        $groups = $searchCriteria->getFilterGroups();
        $enabledFilter = $this->filterBuilder
            ->setField(DeliveryChannelInterface::IS_ACTIVE)
            ->setValue(Status::ENABLED)
            ->create();

        $this->filterGroupBuilder->addFilter($enabledFilter);
        $groups[] = $this->filterGroupBuilder->create();

        $searchCriteria->setFilterGroups($groups);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     */
    private function addSortOrder(SearchCriteriaInterface $searchCriteria): void
    {
        $sortOrderArray = $searchCriteria->getSortOrders();
        $sortOrderArray[] = $this->sortOrderBuilder
            ->setField(DeliveryChannelInterface::PRIORITY)
            ->setDirection(SortOrder::SORT_ASC)
            ->create();

        $searchCriteria->setSortOrders($sortOrderArray);
    }
}
