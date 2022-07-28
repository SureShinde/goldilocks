<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Pricing\InventoryConfigurableProduct\Price\LowestPriceOptionsProvider;

/**
 * Configurable product lowest options price provider stock status base select processor plugin
 */
if (version_compare(\Magento\Framework\App\ObjectManager::getInstance()->get('\\Magento\\Framework\\App\\ProductMetadata')->getVersion(), '2.3.4', '>=')) :

include_once __DIR__.'/2.3.4/StockStatusBaseSelectProcessor.php';

else :

include_once __DIR__.'/2.3.1/StockStatusBaseSelectProcessor.php';

endif;
