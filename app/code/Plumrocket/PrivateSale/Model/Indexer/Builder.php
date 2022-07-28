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

namespace Plumrocket\PrivateSale\Model\Indexer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\SaveHandler\Batch;
use Plumrocket\PrivateSale\Model\Config\Source\EventStatus;
use Traversable;

/**
 * @since 5.0.0
 */
class Builder
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Plumrocket\PrivateSale\Model\Indexer\TableNameResolver
     */
    private $tableNameResolver;

    /**
     * @var \Magento\Framework\Indexer\SaveHandler\Batch
     */
    private $batch;

    /**
     * @var \Plumrocket\PrivateSale\Model\Indexer\IndexStructure
     */
    private $indexStructure;

    /**
     * @var string
     */
    private $connection;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @param \Magento\Framework\App\ResourceConnection               $resourceConnection
     * @param \Plumrocket\PrivateSale\Model\Indexer\TableNameResolver $tableNameResolver
     * @param \Magento\Framework\Indexer\SaveHandler\Batch            $batch
     * @param \Plumrocket\PrivateSale\Model\Indexer\IndexStructure    $indexStructure
     * @param string                                                  $connection
     * @param int                                                     $batchSize
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        TableNameResolver $tableNameResolver,
        Batch $batch,
        IndexStructure $indexStructure,
        $connection = ResourceConnection::DEFAULT_CONNECTION,
        int $batchSize = 20000
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->tableNameResolver = $tableNameResolver;
        $this->batch = $batch;
        $this->indexStructure = $indexStructure;
        $this->connection = $connection;
        $this->batchSize = $batchSize;
    }

    /**
     * @param array        $productIds
     * @param \Traversable $documents
     * @param int          $status
     * @param bool         $skipClear
     * @return bool
     */
    public function build(array $productIds, Traversable $documents, int $status, bool $skipClear = false) : bool
    {
        if (! $skipClear) {
            $this->clear($productIds, $status);
        }

        return $this->write($documents, $status);
    }

    /**
     * @param array $productIds
     * @param int   $status
     */
    public function clear(array $productIds, int $status)
    {
        $connection = $this->resourceConnection->getConnection($this->connection);
        $table = $this->resourceConnection->getTableName($this->tableNameResolver->getResolvedNameByStatus($status));
        $connection->delete($table, $connection->quoteInto(IndexStructure::PRODUCT_ID . ' IN (?)', $productIds));
    }

    /**
     * Clear all index tables
     */
    public function clearAll()
    {
        $connection = $this->resourceConnection->getConnection($this->connection);
        foreach ([EventStatus::UPCOMING, EventStatus::ACTIVE, EventStatus::ENDED] as $status) {
            $table = $this->resourceConnection->getTableName(
                $this->tableNameResolver->getResolvedNameByStatus($status)
            );
            $connection->delete($table);
        }
    }

    /**\
     * @param array $ids
     * @param int   $status
     */
    public function cleanEventIndex(array $ids, int $status)
    {
        $connection = $this->resourceConnection->getConnection($this->connection);
        $table = $this->resourceConnection->getTableName($this->tableNameResolver->getResolvedNameByStatus($status));
        $connection->delete($table, $connection->quoteInto(IndexStructure::EVENT_ID . ' IN (?)', $ids));
    }

    /**
     * @param \Traversable $documents
     * @param int          $status
     * @return bool
     */
    public function write(Traversable $documents, int $status) : bool
    {
        if (! $documents) {
            return false;
        }

        $connection = $this->resourceConnection->getConnection();
        $table = $this->resourceConnection->getTableName($this->tableNameResolver->getResolvedNameByStatus($status));
        $columns = $this->indexStructure->getColumns();

        $affectedRows = 0;
        foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
            $affectedRows += $connection->insertArray($table, $columns, $batchDocuments);
        }

        return count($documents) === $affectedRows;
    }
}
