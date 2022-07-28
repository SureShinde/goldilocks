<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryOrder;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateOrderSearchResultInterface;
use Amasty\DeliveryDateManager\Api\Data\DeliveryDateOrderSearchResultInterfaceFactory;
use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryDateOrder\Collection as DeliveryCollection;
use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryDateOrder\CollectionFactory as DeliveryCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

class GetList
{
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var DeliveryCollectionFactory
     */
    private $sourceItemCollectionFactory;

    /**
     * @var DeliveryDateOrderSearchResultInterfaceFactory
     */
    private $searchResultsFactory;

    public function __construct(
        CollectionProcessorInterface $collectionProcessor,
        DeliveryCollectionFactory $sourceItemCollectionFactory,
        DeliveryDateOrderSearchResultInterfaceFactory $sourceItemSearchResultsFactory
    ) {
        $this->collectionProcessor = $collectionProcessor;
        $this->sourceItemCollectionFactory = $sourceItemCollectionFactory;
        $this->searchResultsFactory = $sourceItemSearchResultsFactory;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return DeliveryDateOrderSearchResultInterface
     */
    public function execute(SearchCriteriaInterface $searchCriteria): DeliveryDateOrderSearchResultInterface
    {
        /** @var DeliveryCollection $collection */
        $collection = $this->sourceItemCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var DeliveryDateOrderSearchResultInterface $searchResult */
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        $searchResult->setSearchCriteria($searchCriteria);

        return $searchResult;
    }
}
