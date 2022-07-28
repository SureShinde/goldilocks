<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryChannelScope;

use Amasty\DeliveryDateManager\Api\DeliveryChannelScopeDataInterface;
use Magento\Framework\DataObject;

class CustomerGroupScopeData extends DataObject implements DeliveryChannelScopeDataInterface
{
    public const SCOPE_CODE = 'customer_group';
    public const GROUP_ID = 'group_id';

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
        return $this->getCustomerGroupId();
    }

    /**
     * @param bool|int|string|null $scopeValue
     */
    public function setScopeValue($scopeValue): void
    {
        $this->setCustomerGroupId($scopeValue);
    }

    /**
     * @return int|null
     */
    public function getCustomerGroupId(): ?int
    {
        $data = $this->_getData(self::GROUP_ID);
        if ($data === null) {
            return null;
        }

        return (int)$data;
    }

    /**
     * @param int|null $groupId
     *
     * @return void
     */
    public function setCustomerGroupId(?int $groupId): void
    {
        $this->setData(self::GROUP_ID, $groupId);
    }
}
