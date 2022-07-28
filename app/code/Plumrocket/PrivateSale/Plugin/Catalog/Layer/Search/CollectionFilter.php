<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket Private Sales and Flash Sales
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Plugin\Catalog\Layer\Search;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer\Search\CollectionFilter as BaseCollectionFilter;
use Magento\Framework\Session\SessionManagerInterface;
use Plumrocket\PrivateSale\Api\PrivateSaleServiceInterface;
use Plumrocket\PrivateSale\Helper\Config;

class CollectionFilter
{
    /**
     * @var Config
     */
    private $configHelper;

    /**
     * @var SessionManagerInterface
     */
    private $customerSession;

    /**
     * @var PrivateSaleServiceInterface
     */
    private $getProductIdsOnPrivateSale;

    /**
     * CollectionFilter constructor.
     *
     * @param Config $configHelper
     * @param SessionManagerInterface $customerSession
     * @param PrivateSaleServiceInterface $getProductIdsOnPrivateSale
     */
    public function __construct(
        Config $configHelper,
        SessionManagerInterface $customerSession,
        PrivateSaleServiceInterface $getProductIdsOnPrivateSale
    ) {
        $this->configHelper = $configHelper;
        $this->customerSession = $customerSession;
        $this->getProductIdsOnPrivateSale = $getProductIdsOnPrivateSale;
    }

    /**
     * @param BaseCollectionFilter $subject
     * @param $result
     * @param $collection
     * @param Category $category
     */
    public function afterFilter(
        BaseCollectionFilter $subject,
        $result,
        $collection,
        Category $category
    ) {
        if ($this->configHelper->isModuleEnabled() && $this->isCustomerGroupDisallowed()) {
            $productIds = $this->getProductIdsOnPrivateSale->getProductIdsOnPrivateSale();

            if ($productIds) {
                $collection->addAttributeToFilter('entity_id', ['nin' => $productIds]);
            }
        }
    }

    /**
     * @return bool
     */
    private function isCustomerGroupDisallowed()
    {
        return in_array(
            $this->customerSession->getCustomerGroupId(),
            $this->configHelper->getDisallowedCustomerGroupsInSearch()
        );
    }
}
