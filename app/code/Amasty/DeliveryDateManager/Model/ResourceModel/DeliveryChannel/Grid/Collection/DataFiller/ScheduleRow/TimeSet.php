<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel\Grid\Collection\DataFiller\ScheduleRow;

use Amasty\DeliveryDateManager\Api\Data\OrderLimitInterface;
use Amasty\DeliveryDateManager\Api\Data\TimeIntervalInterface;
use Amasty\DeliveryDateManager\Model\ResourceModel\OrderLimit;
use Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval as TimeIntervalResource;
use Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval\Set;
use Amasty\DeliveryDateManager\Model\TimeInterval\MinsToTimeConverter;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

class TimeSet
{
    public const SET_NAME = 'set_name';

    /**
     * @var MinsToTimeConverter
     */
    private $minsToTimeConverter;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        MinsToTimeConverter $minsToTimeConverter,
        ResourceConnection $resourceConnection
    ) {
        $this->minsToTimeConverter = $minsToTimeConverter;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param array $scheduleIds
     * @return array where [<schedule_id> => [<set_id> => [<time_interval_data>, ...]]]
     */
    public function getTimeData(array $scheduleIds): array
    {
        $connection = $this->resourceConnection->getConnection();
        $scheduleIntervalSetIds = $this->getIntervalSetIds($scheduleIds);
        $intervalSetIds = array_keys($scheduleIntervalSetIds);
        $intervalsData = [];

        if (!empty($intervalSetIds)) {
            $select = $connection->select()
                ->from(
                    ['main_table' => $this->resourceConnection->getTableName(Set::MAIN_TABLE)],
                    [
                        'id',
                        self::SET_NAME => 'main_table.name'
                    ]
                )->joinLeft(
                    ['interval_rel' => $this->resourceConnection->getTableName(Set::TIME_SET_RELATION_TABLE)],
                    'interval_rel.set_id = main_table.id AND '
                    . 'interval_rel.relation_type = ' . Set::RELATION_TYPE_TIME,
                    []
                )->joinLeft(
                    ['time_interval' => $this->resourceConnection->getTableName(TimeIntervalResource::MAIN_TABLE)],
                    'interval_rel.relation_id = time_interval.interval_id'
                )->joinLeft(
                    ['order_limit' => $this->resourceConnection->getTableName(OrderLimit::MAIN_TABLE)],
                    'order_limit.limit_id = time_interval.limit_id',
                    [OrderLimitInterface::INTERVAL_LIMIT]
                )->where(
                    'main_table.id IN(?)',
                    $intervalSetIds
                )->order('time_interval.position ' . Select::SQL_ASC);

            $data = (array)$connection->fetchAll($select);

            foreach ($data as $itemData) {
                $intervalSetId = $itemData['id'];
                $relatedScheduleIds = $scheduleIntervalSetIds[$intervalSetId];
                $itemData[TimeIntervalInterface::FROM] = $this->minsToTimeConverter->execute(
                    (int)$itemData[TimeIntervalInterface::FROM]
                );
                $itemData[TimeIntervalInterface::TO] = $this->minsToTimeConverter->execute(
                    (int)$itemData[TimeIntervalInterface::TO]
                );

                foreach ($relatedScheduleIds as $scheduleId) {
                    $intervalsData[$scheduleId]['time_intervals'][] = $itemData;
                    $intervalsData[$scheduleId]['name'] = $itemData[self::SET_NAME];
                }
            }
        }

        return $intervalsData;
    }

    /**
     * @param array $scheduleIds
     * @return array where [<interval_set_id> => [<channel_id>, ...]]
     */
    private function getIntervalSetIds(array $scheduleIds): array
    {
        $setIds = [];
        $connection = $this->resourceConnection->getConnection();

        if (!empty($scheduleIds)) {
            $select = $connection->select()
                ->from(
                    ['main_table' => $this->resourceConnection->getTableName(Set::MAIN_TABLE)],
                    ['id']
                )->joinLeft(
                    ['schedule_rel' => $this->resourceConnection->getTableName(Set::TIME_SET_RELATION_TABLE)],
                    'schedule_rel.set_id = main_table.id AND '
                    . 'schedule_rel.relation_type = ' . Set::RELATION_TYPE_SCHEDULE,
                    ['relation_id']
                )->where(
                    'schedule_rel.relation_id IN(?)',
                    $scheduleIds
                )->group(
                    [
                        'main_table.id',
                        'schedule_rel.relation_id'
                    ]
                );

            $data = (array)$connection->fetchAll($select);

            foreach ($data as $item) {
                $setIds[$item['id']][] = $item['relation_id'];
            }
        }

        return $setIds;
    }
}
