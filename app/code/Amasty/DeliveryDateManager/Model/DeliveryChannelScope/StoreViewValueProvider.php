<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryChannelScope;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Model\StoreManagerInterface;

class StoreViewValueProvider implements \Amasty\DeliveryDateManager\Api\ScopeValueProviderInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return (int)$this->storeManager->getStore()->getId();
    }

    /**
     * @param \Magento\Quote\Api\Data\AddressInterface|\Magento\Quote\Model\Quote\Address $address
     * @return int
     */
    public function extractValueFromAddress(\Magento\Quote\Api\Data\AddressInterface $address): int
    {
        return (int)$address->getQuote()->getStoreId();
    }

    /**
     * @param OrderInterface $order
     * @return int
     */
    public function extractValueFromOrder(OrderInterface $order): int
    {
        return (int)$order->getStoreId();
    }
}
