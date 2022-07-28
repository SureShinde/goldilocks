<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Setup;

/**
 * Install schema
 */
class InstallSchema extends \Ecombricks\Common\Setup\AbstractInstallSchema
{

    /**
     * Constructor
     *
     * @param \Ecombricks\StoreInventory\Setup\Operation\InstallSchema $operation
     * @return void
     */
    public function __construct(
        \Ecombricks\StoreInventory\Setup\Operation\InstallSchema $operation
    )
    {
        parent::__construct($operation);
    }

}
