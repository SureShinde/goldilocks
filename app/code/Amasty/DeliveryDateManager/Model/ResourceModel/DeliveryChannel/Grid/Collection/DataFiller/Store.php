<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel\Grid\Collection\DataFiller;

use Amasty\DeliveryDateManager\Api\Data\DeliveryChannelInterface;
use Amasty\DeliveryDateManager\Api\DeliveryChannelScopeDataInterface;
use Amasty\DeliveryDateManager\Model\ResourceModel\Collection\DataFillerInterface;
use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel;
use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel\Grid\Collection;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Store implements DataFillerInterface
{
    /**
     * @param AbstractCollection|Collection $collection
     * @return void
     */
    public function attachData(AbstractCollection $collection): void
    {
        $stores = $this->getStores($collection);

        if (!empty($stores)) {
            foreach ($collection->getItems() as $item) {
                $channelId = $item->getChannelId();
                $channelStores = $stores[$channelId] ?? [];
                $item->setData('store_id', $channelStores);
            }
        }
    }

    /**
     * @param AbstractCollection $collection
     * @return array
     */
    private function getStores(AbstractCollection $collection): array
    {
        $channelIds = $collection->getColumnValues(DeliveryChannelInterface::CHANNEL_ID);
        $stores = [];

        if (!empty($channelIds)) {
            $select = $collection->getConnection()->select()
                ->from($collection->getTable(DeliveryChannel::SCOPE_STORE_TABLE))
                ->where(DeliveryChannelScopeDataInterface::CHANNEL_ID . ' IN(?)', $channelIds);

            $data = (array)$collection->getConnection()->fetchAll($select);

            foreach ($data as $itemData) {
                $channelId = $itemData[DeliveryChannelScopeDataInterface::CHANNEL_ID];
                $stores[$channelId][] = $itemData['store_id'];
            }
        }

        return $stores;
    }
}
