<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Api;

/**
 * Delivery channel search criteria scope processor
 */
interface DeliveryChannelScopeProcessorInterface
{
    /**
     * Process Scope Search Criteria.
     * Add scope filters.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     */
    public function processSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria): void;
}
