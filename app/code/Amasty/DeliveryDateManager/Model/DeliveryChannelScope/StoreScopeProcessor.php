<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryChannelScope;

use Amasty\DeliveryDateManager\Api\DeliveryChannelScopeProcessorInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;

class StoreScopeProcessor implements DeliveryChannelScopeProcessorInterface
{
    /**
     * @var ScopeRegistry
     */
    private $scopeRegistry;

    /**
     * @var FilterGroupBuilder
     */
    private $filterGroupBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    public function __construct(
        ScopeRegistry $scopeRegistry,
        FilterGroupBuilder $filterGroupBuilder,
        FilterBuilder $filterBuilder
    ) {
        $this->scopeRegistry = $scopeRegistry;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     */
    public function processSearchCriteria(SearchCriteriaInterface $searchCriteria): void
    {
        $groups = $searchCriteria->getFilterGroups();

        $storeId = $this->scopeRegistry->getScope(StoreViewScopeData::SCOPE_CODE);

        $scope = $this->filterBuilder
            ->setField(StoreViewScopeData::STORE_ID)
            ->setValue($storeId)
            ->create();
        $noScope = $this->filterBuilder->setConditionType('null')
            ->setField(StoreViewScopeData::STORE_ID)
            ->setValue(true)
            ->create();

        //filters in one group separated by logical OR
        $groups[] = $this->filterGroupBuilder->addFilter($scope)->addFilter($noScope)->create();

        $searchCriteria->setFilterGroups($groups);
    }
}
