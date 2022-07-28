<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\Relations\TimeIntervalChannelRelation;

use Amasty\DeliveryDateManager\Api\Data\TimeIntervalChannelRelationSearchResultsInterface;
use Amasty\DeliveryDateManager\Api\Data\TimeIntervalChannelRelationSearchResultsInterfaceFactory;
use Amasty\DeliveryDateManager\Model\ResourceModel\TimeIntervalChannelRelation\Collection;
use Amasty\DeliveryDateManager\Model\ResourceModel\TimeIntervalChannelRelation\CollectionFactory;
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
     * @var TimeIntervalChannelRelationSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    public function __construct(
        CollectionProcessorInterface $collectionProcessor,
        CollectionFactory $sourceItemCollectionFactory,
        TimeIntervalChannelRelationSearchResultsInterfaceFactory $sourceItemSearchResultsFactory
    ) {
        $this->collectionProcessor = $collectionProcessor;
        $this->sourceItemCollectionFactory = $sourceItemCollectionFactory;
        $this->searchResultsFactory = $sourceItemSearchResultsFactory;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return TimeIntervalChannelRelationSearchResultsInterface
     */
    public function execute(SearchCriteriaInterface $searchCriteria): TimeIntervalChannelRelationSearchResultsInterface
    {
        /** @var Collection $collection */
        $collection = $this->sourceItemCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var TimeIntervalChannelRelationSearchResultsInterface $searchResult */
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        $searchResult->setSearchCriteria($searchCriteria);

        return $searchResult;
    }
}
