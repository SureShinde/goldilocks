<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Observer\InventorySales\Stock;

/**
 * Populate with website sales channels observer plugin
 */
class PopulateWithWebsiteSalesChannelsObserver
{

    /**
     * Store sales channel factory
     *
     * @var \Ecombricks\StoreInventory\Model\InventorySales\StoreSalesChannelFactory
     */
    protected $storeSalesChannelFactory;

    /**
     * Constructor
     *
     * @param \Ecombricks\StoreInventory\Model\InventorySales\StoreSalesChannelFactory $storeSalesChannelFactory
     * @return void
     */
    public function __construct(
        \Ecombricks\StoreInventory\Model\InventorySales\StoreSalesChannelFactory $storeSalesChannelFactory
    )
    {
        $this->storeSalesChannelFactory = $storeSalesChannelFactory;
    }

    /**
     * Around execute
     *
     * @param \Magento\InventorySales\Observer\Stock\PopulateWithWebsiteSalesChannelsObserver $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function aroundExecute(
        \Magento\InventorySales\Observer\Stock\PopulateWithWebsiteSalesChannelsObserver $subject,
        \Closure $proceed,
        \Magento\Framework\Event\Observer $observer
    )
    {
        $event = $observer->getEvent();
        $requestData = $event->getRequest()->getParams();
        $stockExtension = $event->getStock()->getExtensionAttributes();
        $salesChannels = $stockExtension->getSalesChannels();
        $storeSalesChannelType = \Ecombricks\StoreInventory\Api\InventorySalesApi\Data\SalesChannelInterface::TYPE_STORE;
        if (null !== $salesChannels) {
            foreach ($salesChannels as $key => $salesChannel) {
                if ($salesChannel->getType() === $storeSalesChannelType) {
                    unset($salesChannels[$key]);
                }
            }
        }
        $newStoreSalesChannels = $requestData['sales_channels'][$storeSalesChannelType] ?? [];
        if (is_array($newStoreSalesChannels)) {
            foreach ($newStoreSalesChannels as $storeCode) {
                $salesChannels[] = $this->storeSalesChannelFactory->createByStore($storeCode);
            }
        }
        $stockExtension->setSalesChannels($salesChannels);
    }

}
