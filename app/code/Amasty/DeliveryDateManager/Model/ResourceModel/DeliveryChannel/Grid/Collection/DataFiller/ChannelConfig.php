<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel\Grid\Collection\DataFiller;

use Amasty\DeliveryDateManager\Api\Data\ChannelConfigDataInterface;
use Amasty\DeliveryDateManager\Api\Data\DeliveryChannelInterface;
use Amasty\DeliveryDateManager\Model\ResourceModel\Collection\DataFillerInterface;
use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel\Grid\Collection;
use Amasty\DeliveryDateManager\Model\TimeInterval\MinsToTimeConverter;
use Amasty\DeliveryDateManager\Model\ResourceModel\ChannelConfig as ResourceChannelConfig;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class ChannelConfig implements DataFillerInterface
{
    /**
     * @var MinsToTimeConverter
     */
    private $minsToTimeConverter;

    public function __construct(MinsToTimeConverter $minsToTimeConverter)
    {
        $this->minsToTimeConverter = $minsToTimeConverter;
    }

    /**
     * @param AbstractCollection|Collection $collection
     * @return void
     */
    public function attachData(AbstractCollection $collection): void
    {
        $configurationsData = $this->getConfigurationsData($collection);

        if (!empty($configurationsData)) {
            foreach ($collection->getItems() as $item) {
                $configId = $item->getConfigId();
                $configData = $configurationsData[$configId] ?? [];
                $item->setData('channel_config', $configData);
            }
        }
    }

    /**
     * @param AbstractCollection $collection
     * @return array [<channel_id> => <config_data>]
     */
    private function getConfigurationsData(AbstractCollection $collection): array
    {
        $configIds = $collection->getColumnValues(DeliveryChannelInterface::CONFIG_ID);
        $configurationsData = [];

        if (!empty($configIds)) {
            $select = $collection->getConnection()->select()
                ->from($collection->getTable(ResourceChannelConfig::MAIN_TABLE))
                ->where(ChannelConfigDataInterface::ID . ' IN(?)', $configIds);

            $data = (array)$collection->getConnection()->fetchAll($select);

            foreach ($data as $itemData) {
                if (isset($itemData[ChannelConfigDataInterface::SAME_DAY_CUTOFF])) {
                    $itemData[ChannelConfigDataInterface::SAME_DAY_CUTOFF] = $this->minsToTimeConverter
                        ->execute((int)$itemData[ChannelConfigDataInterface::SAME_DAY_CUTOFF]);
                }
                $itemData[ChannelConfigDataInterface::IS_SAME_DAY_AVAILABLE] =
                    (bool)$itemData[ChannelConfigDataInterface::IS_SAME_DAY_AVAILABLE];
                $configurationsData[$itemData[ChannelConfigDataInterface::ID]] = $itemData;
            }
        }

        return $configurationsData;
    }
}
