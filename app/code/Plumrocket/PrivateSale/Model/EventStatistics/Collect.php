<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket Private Sales and Flash Sales
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Model\EventStatistics;

use Plumrocket\PrivateSale\Model\EventStatisticsRepository;
use Plumrocket\PrivateSale\Api\Data\EventStatisticsInterface;
use Plumrocket\PrivateSale\Model\Event\GetEventIdByProductId;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Plumrocket\PrivateSale\Helper\Config as ConfigHelper;
use Magento\Framework\App\ResourceConnection;
use Plumrocket\PrivateSale\Model\ResourceModel\EventStatistics;

class Collect
{
    /**
     * @var EventStatisticsInterface
     */
    private $eventStatistics;

    /**
     * @var EventStatisticsRepository
     */
    private $eventStatisticsRepository;

    /**
     * @var GetEventIdByProductId
     */
    private $getEventIdByProductId;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var ConfigHelper
     */
    private $config;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * Collect constructor.
     * @param ResourceConnection $resourceConnection
     * @param EventStatisticsRepository $eventStatisticsRepository
     * @param EventStatisticsInterface $eventStatistics
     * @param GetEventIdByProductId $getEventIdByProductId
     * @param DateTime $dateTime
     * @param ConfigHelper $config
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        EventStatisticsRepository $eventStatisticsRepository,
        EventStatisticsInterface $eventStatistics,
        GetEventIdByProductId $getEventIdByProductId,
        DateTime $dateTime,
        ConfigHelper $config
    ) {
        $this->eventStatisticsRepository = $eventStatisticsRepository;
        $this->eventStatistics = $eventStatistics;
        $this->getEventIdByProductId = $getEventIdByProductId;
        $this->dateTime = $dateTime;
        $this->config = $config;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param $order
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute($order)
    {
        if (! $this->config->isEventStatisticEnabled()) {
            return;
        }

        $itemCollection = $order->getAllVisibleItems();
        $bulkInsert = [];

        foreach ($itemCollection as $item) {
            $eventId = $this->getEventIdByProductId->execute((int) $item->getProductId());

            if ($eventId) {
                $bulkInsert[] = [
                    'order_id' => (int) $order->getId(),
                    'item_id' => (int) $item->getProductId(),
                    'event_id' => $eventId,
                    'customer_id' => 0,
                    'created_date' => $order->getCreatedAt()
                ];
            }
        }

        $this->saveOrderStatistics($bulkInsert);
    }

    /**
     * @param $bulkData
     */
    private function saveOrderStatistics($bulkData)
    {
        if (empty($bulkData)) {
            return;
        }

        try {
            $tableName = $this->resourceConnection->getTableName(EventStatistics::MAIN_TABLE);
            $this->resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION)
                ->insertMultiple($tableName, $bulkData);
        } catch (\Exception $e) {
        }
    }

    /**
     * @param $eventId
     * @param $customerId
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function customer($eventId, $customerId)
    {
        if (! $this->config->isEventStatisticEnabled()) {
            return;
        }

        if ($eventId && $customerId) {
            $evenStatistics = $this->eventStatistics
                ->setOrderId(0)
                ->setItemId(0)
                ->setEventId((int) $eventId)
                ->setCustomerId((int) $customerId)
                ->setCreatedDate($this->dateTime->gmtDate());

            $this->eventStatisticsRepository->save($evenStatistics);
        }
    }
}
