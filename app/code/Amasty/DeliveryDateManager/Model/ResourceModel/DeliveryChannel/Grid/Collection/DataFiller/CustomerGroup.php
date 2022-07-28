<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel\Grid\Collection\DataFiller;

use Amasty\DeliveryDateManager\Api\Data\DeliveryChannelInterface;
use Amasty\DeliveryDateManager\Api\DeliveryChannelScopeDataInterface;
use Amasty\DeliveryDateManager\Model\ResourceModel\Collection\DataFillerInterface;
use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel;
use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel\Grid\Collection;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class CustomerGroup implements DataFillerInterface
{
    /**
     * @param AbstractCollection|Collection $collection
     * @return void
     */
    public function attachData(AbstractCollection $collection): void
    {
        $customerGroups = $this->getCustomerGroups($collection);

        if (!empty($customerGroups)) {
            foreach ($collection->getItems() as $item) {
                $channelId = $item->getChannelId();
                $channelCustomerGroups = $customerGroups[$channelId] ?? [];
                $item->setData('customer_group_id', $channelCustomerGroups);
            }
        }
    }

    /**
     * @param AbstractCollection $collection
     * @return array
     */
    private function getCustomerGroups(AbstractCollection $collection): array
    {
        $channelIds = $collection->getColumnValues(DeliveryChannelInterface::CHANNEL_ID);
        $customerGroups = [];

        if (!empty($channelIds)) {
            $select = $collection->getConnection()->select()
                ->from($collection->getTable(DeliveryChannel::SCOPE_CUSTOMER_GROUP_TABLE))
                ->where(DeliveryChannelScopeDataInterface::CHANNEL_ID . ' IN(?)', $channelIds);

            $data = (array)$collection->getConnection()->fetchAll($select);

            foreach ($data as $itemData) {
                $channelId = $itemData[DeliveryChannelScopeDataInterface::CHANNEL_ID];
                $customerGroups[$channelId][] = $itemData['group_id'];
            }
        }

        return $customerGroups;
    }
}
