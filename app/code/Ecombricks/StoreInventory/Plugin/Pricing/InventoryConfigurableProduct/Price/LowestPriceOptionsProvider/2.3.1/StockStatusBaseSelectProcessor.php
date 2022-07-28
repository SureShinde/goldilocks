<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Pricing\InventoryConfigurableProduct\Price\LowestPriceOptionsProvider;

if (!version_compare(\Magento\Framework\App\ObjectManager::getInstance()->get('\\Magento\\Framework\\App\\ProductMetadata')->getVersion(), '2.3.4', '>=')) :

/**
 * Configurable product lowest options price provider stock status base select processor plugin
 */
class StockStatusBaseSelectProcessor
{

    /**
     * Process
     *
     * @param \Magento\Framework\DB\Select $select
     * @return \Magento\Framework\DB\Select
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function process(\Magento\Framework\DB\Select $select)
    {
        return $select;
    }

}

endif;
