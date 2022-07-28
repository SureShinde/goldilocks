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

namespace Plumrocket\PrivateSale\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\Reader;
use Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\Structure;
use Plumrocket\PrivateSale\Model\ResourceModel\Event;

/**
 * @since 5.0.0
 */
class CreateEntityToEventIndexTable
{
    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $tableName = Reader::MAIN_TABLE_NAME;

        $table = $setup->getConnection()
            ->newTable($setup->getTable($tableName))
            ->addColumn(
                'event_id',
                Table::TYPE_INTEGER,
                11,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Event Identifier'
            )->addColumn(
                'website_id',
                Table::TYPE_SMALLINT,
                5,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Website Identifier'
            )->addColumn(
                Structure::TYPE,
                Table::TYPE_SMALLINT,
                1,
                ['unsigned' => true, 'nullable' => false],
                'Event Type'
            )->addColumn(
                Structure::ENTITY_ID,
                Table::TYPE_INTEGER,
                11,
                ['unsigned' => true, 'nullable' => false],
                'Catalog Entity Identifier'
            )->addColumn(
                Structure::PRIORITY,
                Table::TYPE_BOOLEAN,
                1,
                ['unsigned' => true, 'nullable' => false],
                'Event Priority'
            )->addColumn(
                Structure::IS_PRIVATE,
                Table::TYPE_BOOLEAN,
                1,
                ['unsigned' => true, 'nullable' => false],
                'Is Event Private'
            )->addColumn(
                'start_date',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => true],
                'Event Start Date'
            )->addColumn(
                'end_date',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => true],
                'Event End Date'
            )->addForeignKey(
                $setup->getFkName(
                    $setup->getTable($tableName),
                    'event_id',
                    $setup->getTable(Event::MAIN_TABLE_NAME),
                    'entity_id'
                ),
                'event_id',
                $setup->getTable(Event::MAIN_TABLE_NAME),
                'entity_id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName(
                    $setup->getTable($tableName),
                    'website_id',
                    $setup->getTable('store_website'),
                    'website_id'
                ),
                'website_id',
                $setup->getTable('store_website'),
                'website_id',
                Table::ACTION_CASCADE
            )->setComment('Event to Catalog Entity Index');

        $setup->getConnection()->createTable($table);
    }
}
