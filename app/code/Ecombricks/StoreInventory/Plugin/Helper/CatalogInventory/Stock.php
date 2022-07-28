<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Helper\CatalogInventory;

if (version_compare(\Magento\Framework\App\ObjectManager::getInstance()->get('\\Magento\\Framework\\App\\ProductMetadata')->getVersion(), '2.3.5', '>=')) :

include_once __DIR__.'/2.3.5/Stock.php';

else :

include_once __DIR__.'/2.3.1/Stock.php';

endif;
