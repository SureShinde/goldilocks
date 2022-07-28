<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel\Grid\Collection\DataFiller;

use Amasty\DeliveryDateManager\Api\Data\DeliveryChannelInterface;
use Amasty\DeliveryDateManager\Api\DeliveryChannelScopeDataInterface;
use Amasty\DeliveryDateManager\Model\ResourceModel\Collection\DataFillerInterface;
use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel;
use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel\Grid\Collection;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class ShippingMethod implements DataFillerInterface
{
    /**
     * @param AbstractCollection|Collection $collection
     * @return void
     */
    public function attachData(AbstractCollection $collection): void
    {
        $shippingMethods = $this->getShippingMethods($collection);

        if (!empty($shippingMethods)) {
            foreach ($collection->getItems() as $item) {
                $channelId = $item->getChannelId();
                $channelShippingMethods = $shippingMethods[$channelId] ?? [];
                $item->setData('shipping_method', $channelShippingMethods);
            }
        }
    }

    /**
     * @param AbstractCollection $collection
     * @return array where [<channel_id> => [<shipping_method_code>, ...]]
     */
    private function getShippingMethods(AbstractCollection $collection): array
    {
        $channelIds = $collection->getColumnValues(DeliveryChannelInterface::CHANNEL_ID);
        $shippingMethods = [];

        if (!empty($channelIds)) {
            $select = $collection->getConnection()->select()
                ->from($collection->getTable(DeliveryChannel::SCOPE_SHIPPING_METHOD_TABLE))
                ->where(DeliveryChannelScopeDataInterface::CHANNEL_ID . ' IN(?)', $channelIds);

            $data = (array)$collection->getConnection()->fetchAll($select);

            foreach ($data as $itemData) {
                $channelId = $itemData[DeliveryChannelScopeDataInterface::CHANNEL_ID];
                $shippingMethods[$channelId][] = $itemData['shipping_method'];
            }
        }

        return $shippingMethods;
    }
}
