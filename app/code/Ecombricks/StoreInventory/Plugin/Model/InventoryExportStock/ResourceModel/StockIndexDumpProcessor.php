<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Model\InventoryExportStock\ResourceModel;

if (version_compare(\Magento\Framework\App\ObjectManager::getInstance()->get('\\Magento\\Framework\\App\\ProductMetadata')->getVersion(), '2.3.2', '>=')) :

include_once __DIR__.'/2.3.2/StockIndexDumpProcessor.php';

else :

include_once __DIR__.'/2.3.1/StockIndexDumpProcessor.php';

endif;
