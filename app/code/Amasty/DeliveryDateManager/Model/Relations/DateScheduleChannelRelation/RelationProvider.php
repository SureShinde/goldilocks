<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\Relations\DateScheduleChannelRelation;

use Amasty\DeliveryDateManager\Api\Data\DateScheduleChannelRelationInterface;
use Amasty\DeliveryDateManager\Api\Data\DateScheduleChannelRelationSearchResultsInterface;

class RelationProvider
{
    /**
     * @var GetList
     */
    private $getList;

    /**
     * @var \Magento\Framework\Api\Search\SearchCriteriaBuilderFactory
     */
    private $criteriaBuilderFactory;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;

    public function __construct(
        GetList $getList,
        \Magento\Framework\Api\Search\SearchCriteriaBuilderFactory $criteriaBuilderFactory,
        \Magento\Framework\Api\FilterBuilder $filterBuilder
    ) {
        $this->getList = $getList;
        $this->criteriaBuilderFactory = $criteriaBuilderFactory;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * @param int[] $channelIds
     *
     * @return DateScheduleChannelRelationSearchResultsInterface
     */
    public function getListByChannelIds(array $channelIds): DateScheduleChannelRelationSearchResultsInterface
    {
        $this->filterBuilder->setField(DateScheduleChannelRelationInterface::DELIVERY_CHANNEL_ID);
        $this->filterBuilder->setConditionType('in');
        $this->filterBuilder->setValue($channelIds);
        /** @var \Magento\Framework\Api\Search\SearchCriteriaBuilder $criteriaBuilder */
        $criteriaBuilder = $this->criteriaBuilderFactory->create();
        $criteriaBuilder->addFilter($this->filterBuilder->create());

        return $this->getList->execute($criteriaBuilder->create());
    }
}
