<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\Relations\TimeIntervalDateScheduleRelation;

use Amasty\DeliveryDateManager\Api\Data\TimeIntervalDateScheduleRelationInterface;
use Amasty\DeliveryDateManager\Api\Data\TimeIntervalDateScheduleRelationSearchResultsInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilderFactory;

class RelationProvider
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
     * @var \Magento\Framework\Api\FilterBuilder
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
     * @param int[] $scheduleIds
     *
     * @return TimeIntervalDateScheduleRelationSearchResultsInterface
     */
    public function getListByDateScheduleIds(array $scheduleIds): TimeIntervalDateScheduleRelationSearchResultsInterface
    {
        $this->filterBuilder->setField(TimeIntervalDateScheduleRelationInterface::DATE_SCHEDULE_ID);
        $this->filterBuilder->setConditionType('in');
        $this->filterBuilder->setValue($scheduleIds);
        /** @var \Magento\Framework\Api\Search\SearchCriteriaBuilder $criteriaBuilder */
        $criteriaBuilder = $this->criteriaBuilderFactory->create();
        $criteriaBuilder->addFilter($this->filterBuilder->create());

        return $this->getList->execute($criteriaBuilder->create());
    }

    /**
     * @param array $timeIds
     * @return TimeIntervalDateScheduleRelationSearchResultsInterface
     */
    public function getListByTimeIds(array $timeIds): TimeIntervalDateScheduleRelationSearchResultsInterface
    {
        $this->filterBuilder->setField(TimeIntervalDateScheduleRelationInterface::TIME_INTERVAL_ID);
        $this->filterBuilder->setConditionType('in');
        $this->filterBuilder->setValue($timeIds);
        /** @var \Magento\Framework\Api\Search\SearchCriteriaBuilder $criteriaBuilder */
        $criteriaBuilder = $this->criteriaBuilderFactory->create();
        $criteriaBuilder->addFilter($this->filterBuilder->create());

        return $this->getList->execute($criteriaBuilder->create());
    }
}
