<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Setup\Operation;

/**
 * Uninstall setup operation
 */
class Uninstall extends \Ecombricks\Common\Setup\Operation\CompoundOperation
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
                'class' => \Ecombricks\Common\Setup\Operation\DropTable::class,
                'sortOrder' => 10,
                'arguments' => [
                    'tableName' => 'ecombricks_store__inventory_stock_sales_channel',
                ],
            ],
        ]
    )
    {
        parent::__construct($operationFactory, $operations);
    }

}
