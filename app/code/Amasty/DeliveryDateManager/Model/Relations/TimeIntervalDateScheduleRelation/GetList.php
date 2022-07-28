<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\Relations\TimeIntervalDateScheduleRelation;

use Amasty\DeliveryDateManager\Api\Data\TimeIntervalDateScheduleRelationSearchResultsInterface;
use Amasty\DeliveryDateManager\Api\Data\TimeIntervalDateScheduleRelationSearchResultsInterfaceFactory;
use Amasty\DeliveryDateManager\Model\ResourceModel\TimeIntervalDateScheduleRelation\Collection;
use Amasty\DeliveryDateManager\Model\ResourceModel\TimeIntervalDateScheduleRelation\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

class GetList
{
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var CollectionFactory
     */
    private $sourceItemCollectionFactory;

    /**
     * @var TimeIntervalDateScheduleRelationSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    public function __construct(
        CollectionProcessorInterface $collectionProcessor,
        CollectionFactory $sourceItemCollectionFactory,
        TimeIntervalDateScheduleRelationSearchResultsInterfaceFactory $sourceItemSearchResultsFactory
    ) {
        $this->collectionProcessor = $collectionProcessor;
        $this->sourceItemCollectionFactory = $sourceItemCollectionFactory;
        $this->searchResultsFactory = $sourceItemSearchResultsFactory;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return TimeIntervalDateScheduleRelationSearchResultsInterface
     */
    public function execute(SearchCriteriaInterface $searchCriteria)
    : TimeIntervalDateScheduleRelationSearchResultsInterface
    {
        /** @var Collection $collection */
        $collection = $this->sourceItemCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var TimeIntervalDateScheduleRelationSearchResultsInterface $searchResult */
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        $searchResult->setSearchCriteria($searchCriteria);

        return $searchResult;
    }
}
