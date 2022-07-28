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

namespace Plumrocket\PrivateSale\Model\Indexer\EntityToEvent;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Store\Model\StoreManagerInterface;
use Plumrocket\PrivateSale\Helper\Preview;
use Plumrocket\PrivateSale\Model\Config\Source\EventStatus;
use Plumrocket\PrivateSale\Model\CurrentDateTime;
use Plumrocket\PrivateSale\Model\Event\GetDataDirectly;

/**
 * @since 5.0.0
 */
class Reader
{
    /**
     * Name of Main Table
     */
    const MAIN_TABLE_NAME = 'plumrocket_private_sale_entity_to_event_index';

    /**
     * @var Preview
     */
    protected $previewHelper;

    /**
     * @var GetDataDirectly
     */
    protected $getDataDirectly;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var string
     */
    private $connection;

    /**
     * @var \Plumrocket\PrivateSale\Model\CurrentDateTime
     */
    private $currentDateTime;

    /**
     * @param \Magento\Framework\App\ResourceConnection           $resourceConnection
     * @param \Magento\Store\Model\StoreManagerInterface          $storeManager
     * @param \Plumrocket\PrivateSale\Model\Event\GetDataDirectly $getDataDirectly
     * @param \Plumrocket\PrivateSale\Helper\Preview              $previewHelper
     * @param \Plumrocket\PrivateSale\Model\CurrentDateTime       $currentDateTime
     * @param string                                              $connection
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        StoreManagerInterface $storeManager,
        GetDataDirectly $getDataDirectly,
        Preview $previewHelper,
        CurrentDateTime $currentDateTime,
        $connection = ResourceConnection::DEFAULT_CONNECTION
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->storeManager = $storeManager;
        $this->connection = $connection;
        $this->previewHelper = $previewHelper;
        $this->getDataDirectly = $getDataDirectly;
        $this->currentDateTime = $currentDateTime;
    }

    /**
     * @param int      $type
     * @param int      $entityId
     * @param int|null $status
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function readOne(int $type, int $entityId, int $status = null)
    {
        $connection = $this->resourceConnection->getConnection($this->connection);
        $select = $this->getSelect($status);
        $select->where(Structure::ENTITY_ID . ' = ?', $entityId);
        $select->where(Structure::TYPE . ' = ?', $type);

        return $connection->fetchRow($select);
    }

    /**
     * @param int      $type
     * @param int|null $status
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function readAll(int $type, int $status = null)
    {
        $connection = $this->resourceConnection->getConnection($this->connection);
        $select = $this->getSelect($status);
        $select->where(Structure::TYPE . ' = ?', $type);

        return $connection->fetchRow($select);
    }

    /**
     * @param int      $type
     * @param array    $entityIds
     * @param int|null $status
     * @param int|null $days
     * @param int|null $limit
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function readByEntities(
        int $type,
        array $entityIds,
        int $status = null,
        int $days = null,
        int $limit = null
    ): array {
        $connection = $this->resourceConnection->getConnection($this->connection);
        $select = $this->getSelect($status, $days);

        $select->where(Structure::TYPE . ' = ?', $type);

        $categoriesCount = count($entityIds);
        if (1 === $categoriesCount) {
            $select->where(Structure::ENTITY_ID . ' = ?', array_values($entityIds)[0]);
        } elseif ($categoriesCount > 1) {
            $select->where(Structure::ENTITY_ID . ' IN (?)', $entityIds);
        }

        if ($limit) {
            $select->limit($limit);
        }

        $includeEntity = [];
        $uniqueEntityEvents = [];
        foreach ($connection->fetchAll($select) as $rowId => $row) {
            if (array_key_exists($row['entity_id'], $includeEntity)) {
                continue;
            }
            $includeEntity[$row['entity_id']] = true;
            $uniqueEntityEvents[$rowId] = $row;
        }
        if ($limit) {
            $uniqueEntityEvents = array_splice($uniqueEntityEvents, 0, $limit);
        }

        return $uniqueEntityEvents;
    }

    /**
     * @param array    $eventIds
     * @param int|null $status
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function readByEvents(
        array $eventIds,
        int $status = null
    ): array {
        $connection = $this->resourceConnection->getConnection($this->connection);
        $select = $this->getSelect($status);

        $eventsCount = count($eventIds);
        if (1 === $eventsCount) {
            $select->where(Structure::EVENT_ID . ' = ?', array_values($eventIds)[0]);
        } elseif ($eventsCount > 1) {
            $select->where(Structure::EVENT_ID . ' IN (?)', $eventIds);
        }

        return $connection->fetchAll($select);
    }

    /**
     * @param int|null $status
     * @param int|null $days
     * @return \Magento\Framework\DB\Select
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getSelect(int $status = null, int $days = null): Select
    {
        $connection = $this->resourceConnection->getConnection($this->connection);
        $tableName = $this->resourceConnection->getTableName(self::MAIN_TABLE_NAME);
        $websiteId = $this->storeManager->getWebsite()->getId();

        $select = $connection
            ->select()
            ->from($tableName)
            ->where(Structure::WEBSITE_ID . ' = ?', $websiteId);

        switch ($status) {
            case EventStatus::ACTIVE:
                $this->addActiveFilter($select, $days);
                break;
            case EventStatus::ENDING_SOON:
                $this->addActiveFilter($select);
                $select->where('end_date < ?', $this->getFutureDateTime($days));
                break;
            case EventStatus::UPCOMING:
                $this->addUpcomingFilter($select);
                break;
            case EventStatus::COMING_SOON:
                $this->addUpcomingFilter($select);
                $select->where('start_date < ?', $this->getFutureDateTime($days));
                break;
            case EventStatus::ENDED:
                $select->where('end_date < ?', $this->currentDateTime->getCurrentGmtDate());
                break;
            case null:
                break;
            default:
                throw new \InvalidArgumentException('Status ' . $status . ' dont known.');
        }

        /** Set priority sorting */
        $select->order([Structure::PRIORITY, Structure::EVENT_ID .' DESC']);

        return $select;
    }

    /**
     * @param int|null $days
     * @return \DateTime|false|string
     */
    private function getFutureDateTime(int $days = null)
    {
        if ($days) {
            $timestamp = $this->currentDateTime->getGmtTimestamp() + ($days * 24 * 3600);
            return date('Y-m-d H:i:s', $timestamp);
        }

        return $this->currentDateTime->getCurrentGmtDate();
    }

    /**
     * @param \Magento\Framework\DB\Select $select
     * @param int|null                     $days
     * @return \Magento\Framework\DB\Select
     */
    private function addActiveFilter(Select $select, int $days = null): Select
    {
        return $select
            ->where('start_date < ?', $this->currentDateTime->getCurrentGmtDate())
            ->where('end_date > ?', $this->getFutureDateTime($days));
    }

    /**
     * @param \Magento\Framework\DB\Select $select
     * @return \Magento\Framework\DB\Select
     */
    private function addUpcomingFilter(Select $select): Select
    {
        return $select->where('start_date > ?', $this->currentDateTime->getCurrentGmtDate());
    }
}
