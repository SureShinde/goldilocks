<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\Relations\DateScheduleChannelRelation;

use Amasty\DeliveryDateManager\Api\Data\DateScheduleChannelRelationSearchResultsInterface;
use Amasty\DeliveryDateManager\Api\Data\DateScheduleChannelRelationSearchResultsInterfaceFactory;
use Amasty\DeliveryDateManager\Model\ResourceModel\DateScheduleChannelRelation\Collection;
use Amasty\DeliveryDateManager\Model\ResourceModel\DateScheduleChannelRelation\CollectionFactory;
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
     * @var DateScheduleChannelRelationSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    public function __construct(
        CollectionProcessorInterface $collectionProcessor,
        CollectionFactory $sourceItemCollectionFactory,
        DateScheduleChannelRelationSearchResultsInterfaceFactory $sourceItemSearchResultsFactory
    ) {
        $this->collectionProcessor = $collectionProcessor;
        $this->sourceItemCollectionFactory = $sourceItemCollectionFactory;
        $this->searchResultsFactory = $sourceItemSearchResultsFactory;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return DateScheduleChannelRelationSearchResultsInterface
     */
    public function execute(SearchCriteriaInterface $searchCriteria): DateScheduleChannelRelationSearchResultsInterface
    {
        /** @var Collection $collection */
        $collection = $this->sourceItemCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var DateScheduleChannelRelationSearchResultsInterface $searchResult */
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        $searchResult->setSearchCriteria($searchCriteria);

        return $searchResult;
    }
}
