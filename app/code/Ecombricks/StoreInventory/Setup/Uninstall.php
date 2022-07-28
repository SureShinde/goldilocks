<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Setup;

/**
 * Uninstall
 */
class Uninstall extends \Ecombricks\Common\Setup\AbstractUninstall
{

    /**
     * Constructor
     *
     * @param \Ecombricks\StoreInventory\Setup\Operation\Uninstall $operation
     * @return void
     */
    public function __construct(
        \Ecombricks\StoreInventory\Setup\Operation\Uninstall $operation
    )
    {
        parent::__construct($operation);
    }

}
