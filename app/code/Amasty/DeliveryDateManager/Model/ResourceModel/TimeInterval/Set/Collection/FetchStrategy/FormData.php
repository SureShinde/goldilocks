<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval\Set\Collection\FetchStrategy;

use Amasty\DeliveryDateManager\Api\Data\OrderLimitInterface;
use Amasty\DeliveryDateManager\Api\Data\TimeIntervalInterface;
use Amasty\DeliveryDateManager\Model\ResourceModel\OrderLimit;
use Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval as TimeIntervalResource;
use Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval\Set;
use Amasty\DeliveryDateManager\Model\TimeInterval\MinsToTimeConverter;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\DB\Select;

class FormData implements FetchStrategyInterface
{
    public const JOIN_FLAG_NAME = 'joined_';
    public const INTERVAL_REL_ALIAS = 'int_rel';
    public const INTERVAL_ALIAS = 'interval';
    public const ORDER_LIMIT_ALIAS = 'olim';
    public const TIME_INTERVALS = 'time_intervals';

    /**
     * @var array
     */
    private $timeIntervalKeys;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MinsToTimeConverter
     */
    private $minsToTimeConverter;

    public function __construct(
        ResourceConnection $resourceConnection,
        MinsToTimeConverter $minsToTimeConverter,
        array $timeIntervalKeys = [
            TimeIntervalInterface::INTERVAL_ID,
            TimeIntervalInterface::FROM,
            TimeIntervalInterface::TO,
            TimeIntervalInterface::LABEL,
            OrderLimitInterface::INTERVAL_LIMIT
        ]
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->minsToTimeConverter = $minsToTimeConverter;
        $this->timeIntervalKeys = $timeIntervalKeys;
    }

    /**
     * Fetch time set form data
     *
     * @param Select $select
     * @param array $bindParams
     * @return array
     */
    public function fetchAll(Select $select, array $bindParams = []): array
    {
        $data = [];

        $this->joinTimeIntervals($select);
        $select->order(TimeIntervalInterface::POSITION);
        $result = (array)$select->getConnection()->fetchAll($select, $bindParams);

        foreach ($result as $item) {
            $setId = $item['id'];
            $item[TimeIntervalInterface::FROM] = $this->minsToTimeConverter->execute(
                (int)$item[TimeIntervalInterface::FROM]
            );
            $item[TimeIntervalInterface::TO] = $this->minsToTimeConverter->execute(
                (int)$item[TimeIntervalInterface::TO]
            );

            if (!isset($data[$setId])) {
                $data[$setId] = [
                    'id' => $setId,
                    'name' => $item['name'],
                    self::TIME_INTERVALS => []
                ];
            }

            $data[$setId][self::TIME_INTERVALS][] = array_intersect_key(
                $item,
                array_flip($this->timeIntervalKeys)
            );
        }

        return $data;
    }

    /**
     * @param Select $select
     */
    public function joinTimeIntervals(Select $select): void
    {
        $select
            ->joinLeft(
                [self::INTERVAL_REL_ALIAS => $this->resourceConnection->getTableName(Set::TIME_SET_RELATION_TABLE)],
                'int_rel.set_id = main_table.id AND '
                . 'int_rel.relation_type = ' . Set::RELATION_TYPE_TIME,
                []
            )->joinLeft(
                [self::INTERVAL_ALIAS => $this->resourceConnection->getTableName(TimeIntervalResource::MAIN_TABLE)],
                'int_rel.relation_id = interval.interval_id',
                [
                    TimeIntervalInterface::INTERVAL_ID,
                    TimeIntervalInterface::FROM,
                    TimeIntervalInterface::TO,
                    TimeIntervalInterface::LABEL,
                    TimeIntervalInterface::LIMIT_ID
                ]
            )->joinLeft(
                [self::ORDER_LIMIT_ALIAS => $this->resourceConnection->getTableName(OrderLimit::MAIN_TABLE)],
                'interval.limit_id = olim.limit_id',
                [
                    OrderLimitInterface::INTERVAL_LIMIT
                ]
            );
    }
}
