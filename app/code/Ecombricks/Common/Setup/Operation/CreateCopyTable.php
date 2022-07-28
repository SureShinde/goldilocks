<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\Common\Setup\Operation;

/**
 * Create copy table setup operation
 */
class CreateCopyTable extends \Ecombricks\Common\Setup\Operation\CreateTable
{

    /**
     * Origin table name
     *
     * @var string
     */
    protected $originTableName;

    /**
     * Columns map
     *
     * @var array
     */
    protected $columnsMap;

    /**
     * Origin table status
     *
     * @var array
     */
    protected $originTableStatus;

    /**
     * Constructor
     *
     * @param string $originTableName
     * @param array $columnsMap
     * @param string $tableName
     * @param string|null $tableComment
     * @return void
     */
    public function __construct(
        string $originTableName,
        array $columnsMap,
        string $tableName,
        string $tableComment = null
    )
    {
        $this->originTableName = $originTableName;
        $this->columnsMap = $columnsMap;
        parent::__construct($tableName, $tableComment);
    }

    /**
     * Get origin table status
     *
     * @return array
     */
    protected function getOriginTableStatus(): array
    {
        if ($this->originTableStatus !== null) {
            return $this->originTableStatus;
        }
        return $this->originTableStatus = $this->getConnection()->showTableStatus($this->getTable($this->originTableName));
    }

    /**
     * Get origin table engine
     *
     * @return string
     */
    protected function getOriginTableEngine(): string
    {
        $originTableStatus = $this->getOriginTableStatus();
        return $originTableStatus['Engine'];
    }

    /**
     * Get origin table comment
     *
     * @return string
     */
    protected function getOriginTableComment(): string
    {
        $originTableStatus = $this->getOriginTableStatus();
        return $originTableStatus['Comment'];
    }

    /**
     * Get comment
     *
     * @return string
     */
    protected function getTableComment(): string
    {
        if ($this->tableComment !== null) {
            return $this->tableComment;
        }
        return $this->tableComment = $this->getOriginTableComment();
    }

    /**
     * Get columns
     *
     * @return array
     */
    protected function getColumns(): array
    {
        if ($this->columns !== null) {
            return $this->columns;
        }
        $this->columns = [];
        $connection = $this->getConnection();
        $columns = $connection->describeTable($this->getTable($this->originTableName));
        foreach ($columns as $column) {
            $column = $connection->getColumnCreateByDescribe($column);
            if (!empty($this->columnsMap[$column['name']])) {
                $column = array_merge(
                    $column,
                    array_filter(
                        $this->columnsMap[$column['name']],
                        function($key) {
                            return in_array($key, ['name', 'type', 'length', 'options', 'comment']);
                        },
                        ARRAY_FILTER_USE_KEY
                    )
                );
            }
            $this->columns[] = $column;
        }
        return $this->columns;
    }

    /**
     * Get indexes
     *
     * @return array
     */
    protected function getIndexes(): array
    {
        if ($this->indexes !== null) {
            return $this->indexes;
        }
        $this->indexes = [];
        $indexes = $this->getConnection()->getIndexList($this->getTable($this->originTableName));
        foreach ($indexes as $index) {
            foreach ($index['COLUMNS_LIST'] as &$indexColumn) {
                if (!empty($this->columnsMap[$indexColumn])) {
                    $indexColumn = $this->columnsMap[$indexColumn]['name'];
                }
            }
            $this->indexes[] = $index;
        }
        return $this->indexes;
    }

    /**
     * Get foreign keys
     *
     * @return array
     */
    protected function getForeignKeys(): array
    {
        if ($this->foreignKeys !== null) {
            return $this->foreignKeys;
        }
        $this->foreignKeys = [];
        $foreignKeys = $this->getConnection()->getForeignKeys($this->getTable($this->originTableName));
        foreach ($foreignKeys as &$foreignKey) {
            if (!empty($this->columnsMap[$foreignKey['COLUMN_NAME']])) {
                $column = $this->columnsMap[$foreignKey['COLUMN_NAME']];
                $foreignKey['COLUMN_NAME'] = $column['name'];
                $foreignKey['REF_TABLE_NAME'] = $column['ref_table_name'];
                $foreignKey['REF_COLUMN_NAME'] = $column['ref_name'];
            }
            $this->foreignKeys[] = $foreignKey;
        }
        return $this->foreignKeys;
    }

    /**
     * Create
     *
     * @return \Magento\Framework\DB\Ddl\Table
     */
    protected function createTableDdl(): \Magento\Framework\DB\Ddl\Table
    {
        $tableDdl = parent::createTableDdl();
        $tableDdl->setOption('type', $this->getOriginTableEngine());
        return $tableDdl;
    }

}
