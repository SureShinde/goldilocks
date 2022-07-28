<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryChannelScope;

use Amasty\DeliveryDateManager\Api\DeliveryChannelScopeProcessorInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;

class ShippingMethodProcessor implements DeliveryChannelScopeProcessorInterface
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

        $shippingMethod = $this->scopeRegistry->getScope(ShippingMethodScopeData::SCOPE_CODE);

        if ($shippingMethod !== null) {
            $scope = $this->filterBuilder
                ->setField(ShippingMethodScopeData::SHIPPING_METHOD)
                ->setValue($shippingMethod)
                ->create();
            $this->filterGroupBuilder->addFilter($scope);
        }
        $noScope = $this->filterBuilder->setConditionType('null')
            ->setField(ShippingMethodScopeData::SHIPPING_METHOD)
            ->setValue(true)
            ->create();
        $this->filterGroupBuilder->addFilter($noScope);

        //filters in one group separated by logical OR
        $groups[] = $this->filterGroupBuilder->create();

        $searchCriteria->setFilterGroups($groups);
    }
}
