<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel\Grid\Collection\DataFiller;

use Amasty\DeliveryDateManager\Api\Data\DateScheduleChannelRelationInterface;
use Amasty\DeliveryDateManager\Api\Data\DateScheduleInterface;
use Amasty\DeliveryDateManager\Api\Data\DeliveryChannelInterface;
use Amasty\DeliveryDateManager\Api\Data\OrderLimitInterface;
use Amasty\DeliveryDateManager\Model\DateSchedule\Type\ConvertToOutput;
use Amasty\DeliveryDateManager\Model\ResourceModel\Collection\DataFillerInterface;
use Amasty\DeliveryDateManager\Model\ResourceModel\DateSchedule;
use Amasty\DeliveryDateManager\Model\ResourceModel\DateScheduleChannelRelation;
use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel\Grid\Collection;
use Amasty\DeliveryDateManager\Model\ResourceModel\OrderLimit;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel\Grid\Collection\DataFiller\ScheduleRow\TimeSet;

class ScheduleRow implements DataFillerInterface
{
    public const LIMIT_NAME = 'order_limit_name';
    public const TIME_SET_NAME = 'time_set_name';
    public const TIME_SET = 'time_set';

    /**
     * @var ConvertToOutput
     */
    private $outputConverter;

    /**
     * @var TimeSet
     */
    private $timeSetFiller;

    public function __construct(
        ConvertToOutput $outputConverter,
        TimeSet $timeSetFiller
    ) {
        $this->outputConverter = $outputConverter;
        $this->timeSetFiller = $timeSetFiller;
    }

    /**
     * @param AbstractCollection|Collection $collection
     * @return void
     */
    public function attachData(AbstractCollection $collection): void
    {
        $schedulesData = $this->getSchedulesData($collection);

        if (!empty($schedulesData)) {
            foreach ($collection->getItems() as $item) {
                $channelId = $item->getChannelId();
                $channelSchedulesData = $schedulesData[$channelId] ?? [];
                $item->setData('schedules_row', $channelSchedulesData);
            }
        }
    }

    /**
     * @param AbstractCollection $collection
     * @return array where [<channel_id> => [<schedule_data>, ...]]
     */
    private function getSchedulesData(AbstractCollection $collection): array
    {
        $channelIds = $collection->getColumnValues(DeliveryChannelInterface::CHANNEL_ID);
        $schedulesData = [];

        if (!empty($channelIds)) {
            $select = $collection->getConnection()->select()
                ->from(
                    ['main_table' => $collection->getTable(DateSchedule::MAIN_TABLE)]
                )->joinInner(
                    ['channel_rel' => $collection->getTable(DateScheduleChannelRelation::MAIN_TABLE)],
                    'channel_rel.date_schedule_id = main_table.schedule_id',
                    [DateScheduleChannelRelationInterface::DELIVERY_CHANNEL_ID]
                )->joinLeft(
                    ['order_limit' => $collection->getTable(OrderLimit::MAIN_TABLE)],
                    'order_limit.limit_id = main_table.limit_id',
                    [
                        self::LIMIT_NAME => OrderLimitInterface::NAME,
                        OrderLimitInterface::DAY_LIMIT,
                        OrderLimitInterface::INTERVAL_LIMIT
                    ]
                )->where(
                    'channel_rel.delivery_channel_id IN(?)',
                    $channelIds
                )->order('main_table.is_available ' . Select::SQL_DESC);

            $data = (array)$collection->getConnection()->fetchAll($select);
            $timeData = $this->getTimeData($data);

            foreach ($data as $itemData) {
                $channelId = $itemData[DateScheduleChannelRelationInterface::DELIVERY_CHANNEL_ID];
                $scheduleId = $itemData[DateScheduleInterface::SCHEDULE_ID];
                $type = (int)$itemData[DateScheduleInterface::TYPE];
                $itemData[DateScheduleInterface::IS_AVAILABLE] = (bool)$itemData[DateScheduleInterface::IS_AVAILABLE];
                $itemData[DateScheduleInterface::FROM] = $this->outputConverter->execute(
                    $type,
                    $itemData[DateScheduleInterface::FROM]
                );
                $itemData[DateScheduleInterface::TO] = $this->outputConverter->execute(
                    $type,
                    $itemData[DateScheduleInterface::TO]
                );
                $itemData[self::TIME_SET] = $timeData[$scheduleId] ?? [];
                $itemData[self::TIME_SET_NAME] = $timeData[$scheduleId]['name'] ?? '';
                $schedulesData[$channelId][] = $itemData;
            }
        }

        return $schedulesData;
    }

    /**
     * @param array $schedulesData
     * @return array
     */
    private function getTimeData(array $schedulesData): array
    {
        $scheduleIds = [];

        foreach ($schedulesData as $scheduleData) {
            $scheduleIds[] = $scheduleData[DateScheduleInterface::SCHEDULE_ID];
        }

        return empty($scheduleIds)
            ? []
            : $this->timeSetFiller->getTimeData($scheduleIds);
    }
}
