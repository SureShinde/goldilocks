<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryChannel;

use Amasty\DeliveryDateManager\Api\Data\DeliveryChannelSearchResultsInterface;
use Amasty\DeliveryDateManager\Model\DeliveryChannel\Command\GetList;
use Magento\Framework\Api\Search\SearchCriteriaFactory;

class GetChannelByCurrentScopes
{
    /**
     * @var Command\GetList
     */
    private $getList;

    /**
     * @var SearchCriteriaFactory
     */
    private $searchCriteriaFactory;

    /**
     * @var \Amasty\DeliveryDateManager\Api\DeliveryChannelScopeProcessorInterface[]
     */
    private $scopeProcessors;

    public function __construct(
        GetList $getList,
        SearchCriteriaFactory $searchCriteriaFactory,
        array $scopeProcessors = []
    ) {
        $this->getList = $getList;
        $this->searchCriteriaFactory = $searchCriteriaFactory;
        $this->scopeProcessors = $scopeProcessors;
    }

    /**
     * @return DeliveryChannelSearchResultsInterface
     */
    public function execute(): DeliveryChannelSearchResultsInterface
    {
        $searchCriteria = $this->searchCriteriaFactory->create();
        foreach ($this->scopeProcessors as $processor) {
            $processor->processSearchCriteria($searchCriteria);
        }

        return $this->getList->execute($searchCriteria);
    }
}
