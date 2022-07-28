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
 * @package     Plumrocket_PrivateSale
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Model\Indexer\EventStatistic;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Plumrocket\PrivateSale\Helper\Preview;
use Plumrocket\PrivateSale\Model\Event\GetDataDirectly;
use Plumrocket\PrivateSale\Model\EventStatistics;

/**
 * @since 5.0.0
 */
class Reader
{
    /**
     * Name of Main Table
     */
    const MAIN_TABLE_NAME = 'plumrocket_private_sale_statistic_index';

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
     * @var string
     */
    private $connection;

    /**
     * @var \Plumrocket\PrivateSale\Model\Indexer\EventStatistic\IndexRowFactory
     */
    private $indexRowFactory;

    /**
     * @param \Magento\Framework\App\ResourceConnection                            $resourceConnection
     * @param \Plumrocket\PrivateSale\Model\Event\GetDataDirectly                  $getDataDirectly
     * @param \Plumrocket\PrivateSale\Helper\Preview                               $previewHelper
     * @param \Plumrocket\PrivateSale\Model\Indexer\EventStatistic\IndexRowFactory $indexRowFactory
     * @param string                                                               $connection
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        GetDataDirectly $getDataDirectly,
        Preview $previewHelper,
        IndexRowFactory $indexRowFactory,
        $connection = ResourceConnection::DEFAULT_CONNECTION
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->connection = $connection;
        $this->previewHelper = $previewHelper;
        $this->getDataDirectly = $getDataDirectly;
        $this->indexRowFactory = $indexRowFactory;
    }

    /**
     * @param int[] $eventIds
     * @return IndexRow[]
     */
    public function readByEvents(array $eventIds): array
    {
        return $this->readByType($eventIds, EventStatistics::EVENT_TYPE);
    }

    /**
     * @param int[] $categoriesIds
     * @return IndexRow[]
     */
    public function readByHomepages(array $categoriesIds): array
    {
        return $this->readByType($categoriesIds, EventStatistics::HOMEPAGE_TYPE);
    }

    /**
     * @param int[]  $entityIds
     * @param string $type
     * @return IndexRow[]
     */
    public function readByType(array $entityIds, string $type): array
    {
        $connection = $this->resourceConnection->getConnection($this->connection);
        $select = $this->getSelect($entityIds, $type);

        $data = $connection->fetchAll($select);

        return $this->createRowItems($data);
    }

    /**
     * @param int[]  $entityIds
     * @param string $type
     * @return \Magento\Framework\DB\Select
     */
    private function getSelect(array $entityIds, string $type): Select
    {
        $connection = $this->resourceConnection->getConnection($this->connection);
        $tableName = $this->resourceConnection->getTableName(self::MAIN_TABLE_NAME);

        $select = $connection
            ->select()
            ->from($tableName);

        $select->where(Structure::TYPE . ' = ?', $type);

        $eventsCount = count($entityIds);
        if (1 === $eventsCount) {
            $select->where(Structure::ENTITY_ID . ' = ?', array_values($entityIds)[0]);
        } elseif ($eventsCount > 1) {
            $select->where(Structure::ENTITY_ID . ' IN (?)', $entityIds);
        }

        return $select;
    }

    /**
     * @param array $data
     * @return IndexRow[]
     */
    private function createRowItems(array $data): array
    {
        $result = [];
        foreach ($data as $rowData) {
            /** @var IndexRow $indexRow */
            $indexRow = $this->indexRowFactory->create(['rowData' => $rowData]);
            $result[$indexRow->getEntityId()] = $indexRow;
        }

        return $result;
    }
}
