<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ChannelConfig;

use Amasty\DeliveryDateManager\Api\Data\ChannelConfigSearchResultInterface;
use Amasty\DeliveryDateManager\Api\Data\ChannelConfigSearchResultInterfaceFactory;
use Amasty\DeliveryDateManager\Model\ResourceModel\ChannelConfig\Collection;
use Amasty\DeliveryDateManager\Model\ResourceModel\ChannelConfig\CollectionFactory;
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
     * @var ChannelConfigSearchResultInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        CollectionProcessorInterface $collectionProcessor,
        CollectionFactory $collectionFactory,
        ChannelConfigSearchResultInterfaceFactory $sourceItemSearchResultsFactory,
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
     * @return ChannelConfigSearchResultInterface
     */
    public function execute(SearchCriteriaInterface $searchCriteria): ChannelConfigSearchResultInterface
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        foreach ($collection->getItems() as $orderLimit) {
            $this->registry->set($orderLimit->getLimitId(), $orderLimit);
        }
        /** @var ChannelConfigSearchResultInterface $searchResult */
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        $searchResult->setSearchCriteria($searchCriteria);

        return $searchResult;
    }
}
