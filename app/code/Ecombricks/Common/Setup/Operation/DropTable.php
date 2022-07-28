<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\Common\Setup\Operation;

/**
 * Drop table setup operation
 */
class DropTable extends \Ecombricks\Common\Setup\Operation\AbstractOperation
{

    /**
     * Name
     *
     * @var string
     */
    protected $tableName;

    /**
     * Constructor
     *
     * @param string $tableName
     * @return void
     */
    public function __construct(string $tableName)
    {
        $this->tableName = $tableName;
    }

    /**
     * Execute
     *
     * @return \Ecombricks\Common\Setup\Operation\OperationInterface
     */
    public function execute(): \Ecombricks\Common\Setup\Operation\OperationInterface
    {
        $connection = $this->getConnection();
        $table = $this->getTable($this->tableName);
        if (!$connection->isTableExists($table)) {
            return $this;
        }
        $connection->dropTable($table);
        return $this;
    }

}
