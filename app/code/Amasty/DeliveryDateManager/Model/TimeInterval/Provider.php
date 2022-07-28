<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\TimeInterval;

use Amasty\DeliveryDateManager\Api\Data\OrderLimitInterface;
use Amasty\DeliveryDateManager\Api\Data\TimeIntervalInterface;
use Amasty\DeliveryDateManager\Api\Data\TimeIntervalSearchResultsInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilderFactory;
use Magento\Framework\Data\Collection;

class Provider
{
    /**
     * @var GetList
     */
    private $getList;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $criteriaBuilderFactory;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    public function __construct(
        GetList $getList,
        SearchCriteriaBuilderFactory $criteriaBuilderFactory,
        FilterBuilder $filterBuilder
    ) {
        $this->getList = $getList;
        $this->criteriaBuilderFactory = $criteriaBuilderFactory;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * @param int[] $intervalIds
     * @param string $condition
     * @return TimeIntervalSearchResultsInterface
     */
    public function getListByIds(array $intervalIds, string $condition = 'in'): TimeIntervalSearchResultsInterface
    {
        $this->filterBuilder->setField(TimeIntervalInterface::INTERVAL_ID);
        $this->filterBuilder->setConditionType($condition);
        $this->filterBuilder->setValue($intervalIds);
        /** @var SearchCriteriaBuilder $criteriaBuilder */
        $criteriaBuilder = $this->criteriaBuilderFactory->create();
        $criteriaBuilder->addFilter($this->filterBuilder->create());
        $criteriaBuilder->addSortOrder(TimeIntervalInterface::POSITION, Collection::SORT_ORDER_ASC);

        return $this->getList->execute($criteriaBuilder->create());
    }

    /**
     * @param int[] $intervalIds
     * @param string $condition
     * @return TimeIntervalSearchResultsInterface
     */
    public function getAllowedListByIds(
        array $intervalIds,
        string $condition = 'in'
    ): TimeIntervalSearchResultsInterface {
        $idsFilter = $this->filterBuilder
            ->setField(TimeIntervalInterface::INTERVAL_ID)
            ->setConditionType($condition)
            ->setValue($intervalIds)
            ->create();

        $limitFilter = $this->filterBuilder
            ->setField(OrderLimitInterface::INTERVAL_LIMIT)
            ->setConditionType('gt')
            ->setValue(0)
            ->create();

        /** @var SearchCriteriaBuilder $criteriaBuilder */
        $criteriaBuilder = $this->criteriaBuilderFactory->create();
        $criteriaBuilder
            ->addFilter($idsFilter)
            ->addFilter($limitFilter);

        $criteriaBuilder->addSortOrder(TimeIntervalInterface::POSITION, Collection::SORT_ORDER_ASC);

        return $this->getList->execute($criteriaBuilder->create());
    }
}
