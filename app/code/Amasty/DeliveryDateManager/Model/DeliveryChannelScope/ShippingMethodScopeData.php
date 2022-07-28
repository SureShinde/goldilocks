<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryChannelScope;

use Amasty\DeliveryDateManager\Api\DeliveryChannelScopeDataInterface;

class ShippingMethodScopeData extends \Magento\Framework\DataObject implements DeliveryChannelScopeDataInterface
{
    public const SCOPE_CODE = 'shipping_method';
    public const SHIPPING_METHOD = 'shipping_method';

    /**
     * @return int
     */
    public function getScopeId(): int
    {
        return (int)$this->_getData(DeliveryChannelScopeDataInterface::SCOPE_ID);
    }

    /**
     * @param int $scopeId
     */
    public function setScopeId(int $scopeId): void
    {
        $this->setData(DeliveryChannelScopeDataInterface::SCOPE_ID, $scopeId);
    }

    /**
     * @return int
     */
    public function getChannelId(): int
    {
        return (int)$this->_getData(DeliveryChannelScopeDataInterface::CHANNEL_ID);
    }

    /**
     * @param int $channelId
     */
    public function setChannelId(int $channelId): void
    {
        $this->setData(DeliveryChannelScopeDataInterface::CHANNEL_ID, $channelId);
    }

    /**
     * @return bool|int|string|null
     */
    public function getScopeValue()
    {
        return $this->getShippingMethod();
    }

    /**
     * @param bool|int|string|null $scopeValue
     */
    public function setScopeValue($scopeValue): void
    {
        $this->setShippingMethod($scopeValue);
    }

    /**
     * @return string|null
     */
    public function getShippingMethod(): ?string
    {
        return $this->_getData(self::SHIPPING_METHOD);
    }

    /**
     * @param string|null $shippingMethod
     *
     * @return void
     */
    public function setShippingMethod(?string $shippingMethod): void
    {
        $this->setData(self::SHIPPING_METHOD, $shippingMethod);
    }
}
