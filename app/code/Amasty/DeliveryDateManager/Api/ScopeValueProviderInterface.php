<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Api;

use Magento\Sales\Api\Data\OrderInterface;

/**
 * Scope Area value provider (Service Provider Interface - SPI)
 */
interface ScopeValueProviderInterface
{
    /**
     * @return int|string|null
     */
    public function getValue();

    /**
     * @param \Magento\Quote\Api\Data\AddressInterface|\Magento\Quote\Model\Quote\Address $address
     * @return mixed
     */
    public function extractValueFromAddress(\Magento\Quote\Api\Data\AddressInterface $address);

    /**
     * @param OrderInterface $order
     * @return mixed
     */
    public function extractValueFromOrder(OrderInterface $order);
}
