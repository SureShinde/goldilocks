<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Api\InventorySalesApi\Frontend;

use Ecombricks\StoreInventory\Api\InventorySalesApi\Data\SalesChannelInterface;
use Ecombricks\StoreInventory\Api\InventorySalesApi\GetDefaultStockInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface as SalesChannelInterfaceAlias;
use Magento\InventorySalesApi\Api\GetStockBySalesChannelInterface as GetStockBySalesChannelInterfaceCore;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Get stock by sales channel interface plugin
 */
class GetStockBySalesChannelInterface
{

    /**
     * @var GetDefaultStockInterface
     */
    protected $getDefaultStock;

    /** @var StoreManagerInterface  */
    protected $storeManager;

    /**
     * @param GetDefaultStockInterface $getDefaultStock
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        GetDefaultStockInterface $getDefaultStock,
        StoreManagerInterface $storeManager
    ) {
        $this->getDefaultStock = $getDefaultStock;
        $this->storeManager = $storeManager;
    }

    /**
     * @param GetStockBySalesChannelInterfaceCore $subject
     * @param SalesChannelInterfaceAlias $salesChannel
     * @return SalesChannelInterfaceAlias[]
     */
    public function beforeExecute(
        GetStockBySalesChannelInterfaceCore $subject,
        SalesChannelInterfaceAlias $salesChannel
    ) {
        if (
            in_array(
                $salesChannel->getType(),
                [SalesChannelInterface::TYPE_STORE, SalesChannelInterfaceAlias::TYPE_WEBSITE]
            ) && $salesChannel->getCode() == Store::ADMIN_CODE
        ) {
            return [$salesChannel];
        } else {
            $currentStoreCode = $this->storeManager->getStore()->getCode() ?? 'default';
            $salesChannel->setType(SalesChannelInterface::TYPE_STORE);
            $salesChannel->setCode($currentStoreCode);
            return [$salesChannel];
        }
    }

    /**
     * @param GetStockBySalesChannelInterfaceCore $subject
     * @param $result
     * @param SalesChannelInterface $salesChannel
     * @return \Magento\InventoryApi\Api\Data\StockInterface|mixed
     */
    public function afterExecute(
        GetStockBySalesChannelInterfaceCore $subject,
        $result,
        SalesChannelInterfaceAlias $salesChannel
    ) {
        if (
            in_array($salesChannel->getType(), [SalesChannelInterface::TYPE_STORE, SalesChannelInterfaceAlias::TYPE_WEBSITE]) &&
            Store::ADMIN_CODE === $salesChannel->getCode()
        ) {
            return $this->getDefaultStock->execute();
        }
        return $result;
    }
}
