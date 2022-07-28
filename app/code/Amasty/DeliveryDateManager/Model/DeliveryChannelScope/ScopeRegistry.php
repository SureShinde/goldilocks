<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryChannelScope;

use Magento\Sales\Api\Data\OrderInterface;

class ScopeRegistry
{
    /**
     * @var array
     */
    private $scopesData = [];

    /**
     * @var \Amasty\DeliveryDateManager\Api\ScopeValueProviderInterface[]
     */
    private $scopeValueProviders;

    public function __construct(array $scopeValueProviders)
    {
        $this->scopeValueProviders = $scopeValueProviders;
    }

    /**
     * @param string $scopeCode
     *
     * @return mixed|null
     */
    public function getScope(string $scopeCode)
    {
        if (!array_key_exists($scopeCode, $this->scopesData)) {
            $this->scopesData[$scopeCode] = $this->getScopeValueFromProvider($scopeCode);
        }

        return $this->scopesData[$scopeCode];
    }

    /**
     * @param string $scopeCode
     * @return mixed|null
     */
    private function getScopeValueFromProvider(string $scopeCode)
    {
        if (!isset($this->scopeValueProviders[$scopeCode])) {
            return null;
        }

        return $this->scopeValueProviders[$scopeCode]->getValue();
    }

    /**
     * delete stored data
     */
    public function reset(): void
    {
        $this->scopesData = [];
    }

    /**
     * @param string $scopeCode
     * @param int|string|null $value
     */
    public function setScope(string $scopeCode, $value): void
    {
        $this->scopesData[$scopeCode] = $value;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $address
     */
    public function collectScopesFromQuoteAddress(\Magento\Quote\Model\Quote\Address $address): void
    {
        foreach ($this->scopeValueProviders as $scopeCode => $scopeValueProvider) {
            $this->setScope($scopeCode, $scopeValueProvider->extractValueFromAddress($address));
        }
    }

    /**
     * @param OrderInterface $order
     */
    public function collectScopesFromOrder(OrderInterface $order): void
    {
        foreach ($this->scopeValueProviders as $scopeCode => $scopeValueProvider) {
            $this->setScope($scopeCode, $scopeValueProvider->extractValueFromOrder($order));
        }
    }

    /**
     * @return string
     */
    public function getCacheKey(): string
    {
        $cacheKey = '';
        foreach (array_keys($this->scopeValueProviders) as $scopeCode) {
            $scopeValue = $this->getScope($scopeCode);
            $cacheKey .= $scopeCode . '=' . $scopeValue . '|';
        }

        return $cacheKey;
    }
}
