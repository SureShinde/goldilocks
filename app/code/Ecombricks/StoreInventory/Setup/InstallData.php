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
class InstallData extends \Ecombricks\Common\Setup\AbstractInstallData
{

    /**
     * Constructor
     *
     * @param \Ecombricks\StoreInventory\Setup\Operation\InstallData $operation
     * @return void
     */
    public function __construct(
        \Ecombricks\StoreInventory\Setup\Operation\InstallData $operation
    )
    {
        parent::__construct($operation);
    }

}
