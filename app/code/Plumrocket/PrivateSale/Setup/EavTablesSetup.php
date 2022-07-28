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
 * @package     Plumrocket Private Sales and Flash Sales v4.x.x
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\PrivateSale\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class EavTablesSetup
{
    /**
     * @var SchemaSetupInterface
     */
    protected $setup;

    /**
     * EavTablesSetup constructor.
     *
     * @param SchemaSetupInterface $setup
     */
    public function __construct(SchemaSetupInterface $setup)
    {
        $this->setup = $setup;
    }

    /**
     * @param $entityCode
     * @throws \Zend_Db_Exception
     */
    public function createEavTables($entityCode)
    {
        $this->createEntityTable($entityCode, 'datetime', Table::TYPE_DATETIME);
        $this->createEntityTable($entityCode, 'int', Table::TYPE_INTEGER);
        $this->createEntityTable($entityCode, 'text', Table::TYPE_TEXT, '128k');
        $this->createEntityTable($entityCode, 'varchar', Table::TYPE_TEXT, 255);
    }

    /**
     * @param      $entityCode
     * @param      $type
     * @param      $valueType
     * @param null $valueLength
     * @throws \Zend_Db_Exception
     */
    protected function createEntityTable($entityCode, $type, $valueType, $valueLength = null)
    {
        $tableName = $entityCode . '_entity_' . $type;

        $table = $this->setup->getConnection()
            ->newTable($this->setup->getTable($tableName))
            ->addColumn(
                'value_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Value ID'
            )
            ->addColumn(
                'attribute_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Attribute ID'
            )
            ->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                $valueLength,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Entity ID'
            )
            ->addColumn(
                'value',
                $valueType,
                null,
                [],
                'Value'
            )
            ->addIndex(
                $this->setup->getIdxName(
                    $tableName,
                    ['entity_id', 'attribute_id', 'store_id'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['entity_id', 'attribute_id', 'store_id'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $this->setup->getIdxName($tableName, ['entity_id']),
                ['entity_id']
            )
            ->addIndex(
                $this->setup->getIdxName($tableName, ['attribute_id']),
                ['attribute_id']
            )
            ->addIndex(
                $this->setup->getIdxName($tableName, ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $this->setup->getFkName(
                    $tableName,
                    'attribute_id',
                    'eav_attribute',
                    'attribute_id'
                ),
                'attribute_id',
                $this->setup->getTable('eav_attribute'),
                'attribute_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $this->setup->getFkName(
                    $tableName,
                    'entity_id',
                    $entityCode,
                    'entity_id'
                ),
                'entity_id',
                $this->setup->getTable($entityCode . '_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $this->setup->getFkName($tableName, 'store_id', 'store', 'store_id'),
                'store_id',
                $this->setup->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Plumrocket PrivateSale ' . strtoupper($type) . ' Value Table');

        $this->setup->getConnection()->createTable($table);
    }
}
