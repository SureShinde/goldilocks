<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\Relations\TimeIntervalChannelRelation;

use Amasty\DeliveryDateManager\Api\Data\TimeIntervalChannelRelationInterface;
use Amasty\DeliveryDateManager\Api\Data\TimeIntervalChannelRelationSearchResultsInterface;
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
     * @param int[] $channelIds
     *
     * @return TimeIntervalChannelRelationSearchResultsInterface
     */
    public function getListByChannelIds(array $channelIds): TimeIntervalChannelRelationSearchResultsInterface
    {
        $this->filterBuilder->setField(TimeIntervalChannelRelationInterface::DELIVERY_CHANNEL_ID);
        $this->filterBuilder->setConditionType('in');
        $this->filterBuilder->setValue($channelIds);
        /** @var \Magento\Framework\Api\Search\SearchCriteriaBuilder $criteriaBuilder */
        $criteriaBuilder = $this->criteriaBuilderFactory->create();
        $criteriaBuilder->addFilter($this->filterBuilder->create());

        return $this->getList->execute($criteriaBuilder->create());
    }

    /**
     * @param array $timeIds
     * @return TimeIntervalChannelRelationSearchResultsInterface
     */
    public function getListByTimeIds(array $timeIds): TimeIntervalChannelRelationSearchResultsInterface
    {
        $this->filterBuilder->setField(TimeIntervalChannelRelationInterface::TIME_INTERVAL_ID);
        $this->filterBuilder->setConditionType('in');
        $this->filterBuilder->setValue($timeIds);
        /** @var \Magento\Framework\Api\Search\SearchCriteriaBuilder $criteriaBuilder */
        $criteriaBuilder = $this->criteriaBuilderFactory->create();
        $criteriaBuilder->addFilter($this->filterBuilder->create());

        return $this->getList->execute($criteriaBuilder->create());
    }
}
