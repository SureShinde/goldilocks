<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DateSchedule;

use Amasty\DeliveryDateManager\Api\Data\DateScheduleSearchResultsInterface;
use Amasty\DeliveryDateManager\Api\Data\DateScheduleSearchResultsInterfaceFactory;
use Amasty\DeliveryDateManager\Model\ResourceModel\DateSchedule\Collection;
use Amasty\DeliveryDateManager\Model\ResourceModel\DateSchedule\CollectionFactory;
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
     * @var DateScheduleSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    public function __construct(
        CollectionProcessorInterface $collectionProcessor,
        CollectionFactory $sourceItemCollectionFactory,
        DateScheduleSearchResultsInterfaceFactory $sourceItemSearchResultsFactory
    ) {
        $this->collectionProcessor = $collectionProcessor;
        $this->sourceItemCollectionFactory = $sourceItemCollectionFactory;
        $this->searchResultsFactory = $sourceItemSearchResultsFactory;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return DateScheduleSearchResultsInterface
     */
    public function execute(SearchCriteriaInterface $searchCriteria): DateScheduleSearchResultsInterface
    {
        /** @var Collection $collection */
        $collection = $this->sourceItemCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var DateScheduleSearchResultsInterface $searchResult */
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        $searchResult->setSearchCriteria($searchCriteria);

        return $searchResult;
    }
}
