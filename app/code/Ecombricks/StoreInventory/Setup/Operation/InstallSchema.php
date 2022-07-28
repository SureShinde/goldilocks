<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Setup\Operation;

/**
 * Install schema setup operation
 */
class InstallSchema extends \Ecombricks\Common\Setup\Operation\CompoundOperation
{

    /**
     * Constructor
     *
     * @param \Ecombricks\Common\Setup\Operation\OperationFactory $operationFactory
     * @param array $operations
     * @return void
     */
    public function __construct(
        \Ecombricks\Common\Setup\Operation\OperationFactory $operationFactory,
        array $operations = [
            [
                'class' => \Ecombricks\Common\Setup\Operation\CreateCopyTable::class,
                'sortOrder' => 10,
                'arguments' => [
                    'originTableName' => 'inventory_stock_sales_channel',
                    'columnsMap' => [],
                    'tableName' => 'ecombricks_store__inventory_stock_sales_channel',
                    'tableComment' => 'Inventory Stock Sales Channel',
                ],
            ],
        ]
    )
    {
        parent::__construct($operationFactory, $operations);
    }

}
