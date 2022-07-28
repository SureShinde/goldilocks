<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryChannel\Command;

use Amasty\DeliveryDateManager\Api\Data\DeliveryChannelSearchResultsInterface;
use Amasty\DeliveryDateManager\Api\Data\DeliveryChannelSearchResultsInterfaceFactory;
use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel\Collection;
use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel\CollectionFactory;
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
    private $collectionFactory;

    /**
     * @var DeliveryChannelSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    public function __construct(
        CollectionProcessorInterface $collectionProcessor,
        CollectionFactory $collectionFactory,
        DeliveryChannelSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->collectionProcessor = $collectionProcessor;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return DeliveryChannelSearchResultsInterface
     */
    public function execute(SearchCriteriaInterface $searchCriteria): DeliveryChannelSearchResultsInterface
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var DeliveryChannelSearchResultsInterface $searchResult */
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        $searchResult->setSearchCriteria($searchCriteria);

        return $searchResult;
    }
}
