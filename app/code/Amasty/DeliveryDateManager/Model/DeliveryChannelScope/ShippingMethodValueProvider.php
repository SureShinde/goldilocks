<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryChannelScope;

use Magento\Sales\Api\Data\OrderInterface;

class ShippingMethodValueProvider implements \Amasty\DeliveryDateManager\Api\ScopeValueProviderInterface
{
    /**
     * @return string
     */
    public function getValue(): string
    {
        return '';
    }

    /**
     * @param \Magento\Quote\Api\Data\AddressInterface|\Magento\Quote\Model\Quote\Address $address
     * @return string
     */
    public function extractValueFromAddress(\Magento\Quote\Api\Data\AddressInterface $address): string
    {
        return (string)$address->getShippingMethod();
    }

    /**
     * @param OrderInterface $order
     * @return string
     */
    public function extractValueFromOrder(OrderInterface $order): string
    {
        return (string)$order->getShippingMethod();
    }
}
