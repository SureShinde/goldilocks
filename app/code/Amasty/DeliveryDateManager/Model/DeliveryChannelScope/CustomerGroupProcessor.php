<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryChannelScope;

use Amasty\DeliveryDateManager\Api\DeliveryChannelScopeProcessorInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;

class CustomerGroupProcessor implements DeliveryChannelScopeProcessorInterface
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
        $customerGroupId = $this->scopeRegistry->getScope(CustomerGroupScopeData::SCOPE_CODE);

        $scope = $this->filterBuilder
                ->setField(CustomerGroupScopeData::GROUP_ID)
                ->setValue($customerGroupId)
                ->create();

        $noScope = $this->filterBuilder->setConditionType('null')
            ->setField(CustomerGroupScopeData::GROUP_ID)
            ->setValue(true)
            ->create();

        $this->filterGroupBuilder->addFilter($scope);
        $this->filterGroupBuilder->addFilter($noScope);

        $groups[] = $this->filterGroupBuilder->create();

        $searchCriteria->setFilterGroups($groups);
    }
}
