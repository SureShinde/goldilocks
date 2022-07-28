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

namespace Plumrocket\PrivateSale\Model\Indexer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\SaveHandler\Batch;
use Magento\Store\Model\StoreManagerInterface;
use Plumrocket\PrivateSale\Helper\Preview;
use Plumrocket\PrivateSale\Model\Event\GetDataDirectly;
use Plumrocket\PrivateSale\Model\Indexer\Product as ProductEventIndexer;

class IndexHandler
{
    /**
     * @var Preview
     */
    protected $previewHelper;

    /**
     * @var GetDataDirectly
     */
    protected $getDataDirectly;

    /**
     * @var Batch
     */
    private $batch;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @var TableNameResolver
     */
    private $tableNameResolver;

    /**
     * @var IndexStructure
     */
    private $indexStructure;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var string
     */
    private $connection;

    /**
     * IndexHandler constructor.
     *
     * @param Batch $batch
     * @param ResourceConnection $resourceConnection
     * @param TableNameResolver $tableNameResolver
     * @param IndexStructure $indexStructure
     * @param StoreManagerInterface $storeManager
     * @param GetDataDirectly $getDataDirectly
     * @param Preview $previewHelper
     * @param string $connection
     * @param int $batchSize
     */
    public function __construct(
        Batch $batch,
        ResourceConnection $resourceConnection,
        TableNameResolver $tableNameResolver,
        IndexStructure $indexStructure,
        StoreManagerInterface $storeManager,
        GetDataDirectly $getDataDirectly,
        Preview $previewHelper,
        $connection = ResourceConnection::DEFAULT_CONNECTION,
        int $batchSize = 20000
    ) {
        $this->batch = $batch;
        $this->resourceConnection = $resourceConnection;
        $this->batchSize = $batchSize;
        $this->tableNameResolver = $tableNameResolver;
        $this->indexStructure = $indexStructure;
        $this->storeManager = $storeManager;
        $this->connection = $connection;
        $this->previewHelper = $previewHelper;
        $this->getDataDirectly = $getDataDirectly;
    }

    /**
     * Save index data
     *
     * @param              $indexName
     * @param \Traversable $documents
     */
    public function saveIndex($indexName, \Traversable $documents)
    {
        $connection = $this->resourceConnection->getConnection($this->connection);
        $tableName = $this->resourceConnection->getTableName($this->tableNameResolver->getResolvedName($indexName));
        $columns = $this->indexStructure->getColumns();

        foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
            $connection->insertArray($tableName, $columns, $batchDocuments);
        }
    }

    /**
     * @param $columnValue
     * @param string $indexName
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function readOne($columnValue, $indexName = ProductEventIndexer::INDEX_NAME)
    {
        if ($this->previewHelper->isAllow()) {
            return current(
                $this->getDataDirectly->execute(IndexStructure::PRODUCT_ID, $columnValue, $indexName, null, 1)
            );
        }

        $connection = $this->resourceConnection->getConnection($this->connection);
        $websiteId = $this->storeManager->getWebsite()->getId();
        $select = $this->getSelect(IndexStructure::PRODUCT_ID, $columnValue, $indexName);

        return $connection->fetchRow(
            $select,
            [IndexStructure::WEBSITE_ID => $websiteId]
        );
    }

    /**
     * Read all rows from index
     *
     * @param        $columnName
     * @param        $columnValue
     * @param string $indexName
     * @param null   $groupBy
     * @param null   $limit
     * @return array
     */
    public function readAll(
        $columnName,
        $columnValue,
        string $indexName = ProductEventIndexer::INDEX_NAME,
        $groupBy = null,
        $limit = null
    ): array {
        if ($this->previewHelper->isAllow()) {
            return $this->getDataDirectly->execute($columnName, $columnValue, $indexName, $groupBy, $limit);
        }

        $connection = $this->resourceConnection->getConnection($this->connection);
        $websiteId = $this->storeManager->getWebsite()->getId();
        $select = $this->getSelect($columnName, $columnValue, $indexName);

        if ($groupBy) {
            $select->group($groupBy);
        }

        if ($limit) {
            $select->limit($limit);
        }

        return $connection->fetchAssoc(
            $select,
            [IndexStructure::WEBSITE_ID => $websiteId]
        );
    }

    /**
     * @param $columnName
     * @param $value
     * @param string $indexName
     * @return \Magento\Framework\DB\Select
     */
    private function getSelect($columnName, $value, $indexName = ProductEventIndexer::INDEX_NAME)
    {
        $connection = $this->resourceConnection->getConnection($this->connection);
        $tableName = $this->resourceConnection->getTableName(
            $this->tableNameResolver->getResolvedName($indexName)
        );

        $where = sprintf(
            $value === '' ? '%s%s%s%s = :%s' : '%s %s (%s) AND %s = :%s',
            $value === '' ?  '' : $columnName,
            $value === '' ? '' : 'IN',
            $value,
            IndexStructure::WEBSITE_ID,
            IndexStructure::WEBSITE_ID
        );

        return $connection->select()
            ->from($tableName)
            ->where($where);
    }
}
