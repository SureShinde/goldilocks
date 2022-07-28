<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\TimeInterval;

use Amasty\DeliveryDateManager\Api\Data\TimeIntervalSearchResultsInterface;
use Amasty\DeliveryDateManager\Api\Data\TimeIntervalSearchResultsInterfaceFactory;
use Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval\Collection;
use Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Store\Model\StoreManagerInterface;

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
     * @var TimeIntervalSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        CollectionProcessorInterface $collectionProcessor,
        CollectionFactory $sourceItemCollectionFactory,
        TimeIntervalSearchResultsInterfaceFactory $sourceItemSearchResultsFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->collectionProcessor = $collectionProcessor;
        $this->sourceItemCollectionFactory = $sourceItemCollectionFactory;
        $this->searchResultsFactory = $sourceItemSearchResultsFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return TimeIntervalSearchResultsInterface
     */
    public function execute(SearchCriteriaInterface $searchCriteria): TimeIntervalSearchResultsInterface
    {
        /** @var Collection $collection */
        $collection = $this->sourceItemCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        $collection->addLabelsToItems((int)$this->storeManager->getStore()->getId());

        /** @var TimeIntervalSearchResultsInterface $searchResult */
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        $searchResult->setSearchCriteria($searchCriteria);

        return $searchResult;
    }
}
