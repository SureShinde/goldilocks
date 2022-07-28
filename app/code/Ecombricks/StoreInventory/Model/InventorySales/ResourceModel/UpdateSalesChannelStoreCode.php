<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Model\InventorySales\ResourceModel;

/**
 * Update sales channel store code
 */
class UpdateSalesChannelStoreCode
{

    /**
     * Connection provider
     *
     * @var \Ecombricks\Common\Model\ResourceModel\ConnectionProvider
     */
    protected $connectionProvider;

    /**
     * Constructor
     *
     * @param \Ecombricks\Common\Model\ResourceModel\ConnectionProvider $connectionProvider
     * @return void
     */
    public function __construct(
        \Ecombricks\Common\Model\ResourceModel\ConnectionProvider $connectionProvider
    )
    {
        $this->connectionProvider = $connectionProvider;
    }

    /**
     * Execute
     *
     * @param string $oldCode
     * @param string $newCode
     * @return void
     */
    public function execute(string $oldCode, string $newCode): void
    {
        $this->connectionProvider->getConnection()->update(
            $this->connectionProvider->getTable('ecombricks_store__inventory_stock_sales_channel'),
            [
                \Magento\InventorySalesApi\Api\Data\SalesChannelInterface::CODE => $newCode,
            ],
            [
                \Magento\InventorySalesApi\Api\Data\SalesChannelInterface::TYPE.' = ?' => \Ecombricks\StoreInventory\Api\InventorySalesApi\Data\SalesChannelInterface::TYPE_STORE,
                \Magento\InventorySalesApi\Api\Data\SalesChannelInterface::CODE.' = ?' => $oldCode,
            ]
        );
    }

}
