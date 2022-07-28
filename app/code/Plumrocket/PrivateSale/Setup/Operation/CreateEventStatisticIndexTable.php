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
use Plumrocket\PrivateSale\Model\Indexer\EventStatistic\Reader;
use Plumrocket\PrivateSale\Model\Indexer\EventStatistic\Structure;

/**
 * @since 5.0.0
 */
class CreateEventStatisticIndexTable
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
                Structure::ID,
                Table::TYPE_INTEGER,
                11,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )->addColumn(
                Structure::ENTITY_ID,
                Table::TYPE_INTEGER,
                11,
                ['unsigned' => true, 'nullable' => false],
                'Event or Homepage (Category) Identifier'
            )->addColumn(
                Structure::TYPE,
                Table::TYPE_TEXT,
                10,
                ['nullable' => false],
                'Statistic Entity Type'
            )->addColumn(
                Structure::NEW_USERS,
                Table::TYPE_INTEGER,
                11,
                ['unsigned' => true, 'nullable' => false],
                'Number of new users'
            )->addColumn(
                Structure::ORDER_COUNT,
                Table::TYPE_INTEGER,
                11,
                ['unsigned' => true, 'nullable' => false],
                'Number of orders with products that on events'
            )->addColumn(
                Structure::TOTAL_REVENUE,
                Table::TYPE_FLOAT,
                11,
                ['unsigned' => true, 'nullable' => false],
                'Number of orders with products that on events'
            )->setComment('Event and Homepage Index');

        $setup->getConnection()->createTable($table);
    }
}
