<?php

declare(strict_types=1);

namespace Amasty\PreOrderRelease\Model\Notification;

use Amasty\PreOrderRelease\Model\ConfigProvider;
use Amasty\PreOrderRelease\Model\ResourceModel\FilterNonBackordersProductIds;
use Amasty\PreOrderRelease\Model\ResourceModel\LoadCustomerBuyedPreorder;

class GetCustomersToSend
{
    public const PRODUCT_ID_KEY = 'product_id';
    public const PRODUCT_NAME_KEY = 'name';
    public const CUSTOMER_EMAIL_KEY = 'customer_email';
    public const STORE_ID_KEY = 'store_id';

    /**
     * @var LoadCustomerBuyedPreorder
     */
    private $loadCustomerBuyedPreorder;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var FilterNonBackordersProductIds
     */
    private $filterNonBackordersProductIds;

    public function __construct(
        ConfigProvider $configProvider,
        LoadCustomerBuyedPreorder $loadCustomerBuyedPreorder,
        FilterNonBackordersProductIds $filterNonBackordersProductIds
    ) {
        $this->loadCustomerBuyedPreorder = $loadCustomerBuyedPreorder;
        $this->configProvider = $configProvider;
        $this->filterNonBackordersProductIds = $filterNonBackordersProductIds;
    }

    /**
     * @param string[] $productIds
     * @return array
     */
    public function execute(array $productIds): array
    {
        $productIds = $this->filterNonBackordersProductIds->execute($productIds);

        if (!$productIds) {
            return [];
        }

        return $this->loadCustomerBuyedPreorder->execute(
            $productIds,
            $this->configProvider->getReleaseOrderStatuses()
        );
    }
}
