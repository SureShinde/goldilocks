<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Model\InventoryShippingAdminUi;

/**
 * Is order source manageable
 */
if (version_compare(\Magento\Framework\App\ObjectManager::getInstance()->get('\\Magento\\Framework\\App\\ProductMetadata')->getVersion(), '2.3.2', '>=')) :

class IsOrderSourceManageable extends \Magento\InventoryShippingAdminUi\Model\IsOrderSourceManageable
{



}

endif;
