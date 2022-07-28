<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\OrderLimit;

use Amasty\DeliveryDateManager\Api\Data\OrderLimitSearchResultInterface;
use Amasty\DeliveryDateManager\Api\Data\OrderLimitSearchResultInterfaceFactory;
use Amasty\DeliveryDateManager\Model\ResourceModel\OrderLimit\Collection;
use Amasty\DeliveryDateManager\Model\ResourceModel\OrderLimit\CollectionFactory;
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
     * @var OrderLimitSearchResultInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        CollectionProcessorInterface $collectionProcessor,
        CollectionFactory $collectionFactory,
        OrderLimitSearchResultInterfaceFactory $sourceItemSearchResultsFactory,
        Registry $registry
    ) {
        $this->collectionProcessor = $collectionProcessor;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $sourceItemSearchResultsFactory;
        $this->registry = $registry;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return OrderLimitSearchResultInterface
     */
    public function execute(SearchCriteriaInterface $searchCriteria): OrderLimitSearchResultInterface
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        foreach ($collection->getItems() as $orderLimit) {
            $this->registry->set($orderLimit->getLimitId(), $orderLimit);
        }
        /** @var OrderLimitSearchResultInterface $searchResult */
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        $searchResult->setSearchCriteria($searchCriteria);

        return $searchResult;
    }
}
