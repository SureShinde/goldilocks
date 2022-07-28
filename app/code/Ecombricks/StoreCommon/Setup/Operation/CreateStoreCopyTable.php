<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreCommon\Setup\Operation;

/**
 * Create store copy table setup operation
 */
class CreateStoreCopyTable extends \Ecombricks\Common\Setup\Operation\CreateCopyTable
{

    /**
     * Constructor
     *
     * @param string $originTableName
     * @param string $tableName
     * @param string|null $tableComment
     * @return void
     */
    public function __construct(
        string $originTableName,
        string $tableName,
        string $tableComment = null
    )
    {
        parent::__construct(
            $originTableName,
            [
                'website_id' => [
                    'name' => 'store_id',
                    'comment' => 'Store ID',
                    'ref_table_name' => 'store',
                    'ref_name' => 'store_id',
                ],
            ],
            $tableName,
            $tableComment
        );
    }

}
