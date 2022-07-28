<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryChannelScope;

use Amasty\DeliveryDateManager\Api\DeliveryChannelScopeDataInterface;

class StoreViewScopeData extends \Magento\Framework\DataObject implements DeliveryChannelScopeDataInterface
{
    public const SCOPE_CODE = 'store';
    public const STORE_ID = 'store_id';

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
        return $this->getStoreId();
    }

    /**
     * @param bool|int|string|null $scopeValue
     */
    public function setScopeValue($scopeValue): void
    {
        $this->setStoreId($scopeValue);
    }

    /**
     * @return int|null
     */
    public function getStoreId(): ?int
    {
        $data = $this->_getData(self::STORE_ID);
        if ($data === null) {
            return null;
        }

        return (int)$data;
    }

    /**
     * @param int|null $storeId
     *
     * @return void
     */
    public function setStoreId(?int $storeId): void
    {
        $this->setData(self::STORE_ID, $storeId);
    }
}
