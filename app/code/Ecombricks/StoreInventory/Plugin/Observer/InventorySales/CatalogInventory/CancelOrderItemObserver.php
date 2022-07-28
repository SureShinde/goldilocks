<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Observer\InventorySales\CatalogInventory;

/**
 * Cancel order item observer plugin
 */
class CancelOrderItemObserver
{

    /**
     * Price indexer
     *
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    protected $priceIndexer;

    /**
     * Sales event factory
     *
     * @var \Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory
     */
    protected $salesEventFactory;

    /**
     * Place reservations for sales event
     *
     * @var \Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface
     */
    protected $placeReservationsForSalesEvent;

    /**
     * Store sales channel factory
     *
     * @var \Ecombricks\StoreInventory\Model\InventorySales\StoreSalesChannelFactory
     */
    protected $storeSalesChannelFactory;

    /**
     * Get items to cancel from order item
     *
     * @var \Magento\InventorySales\Model\GetItemsToCancelFromOrderItem
     */
    protected $getItemsToCancelFromOrderItem;

    /**
     * Constructor
     *
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $priceIndexer
     * @param \Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory $salesEventFactory
     * @param \Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface $placeReservationsForSalesEvent
     * @param \Ecombricks\StoreInventory\Model\InventorySales\StoreSalesChannelFactory $storeSalesChannelFactory
     * @param \Magento\InventorySales\Model\GetItemsToCancelFromOrderItem $getItemsToCancelFromOrderItem
     * @return void
     */
    public function __construct(
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $priceIndexer,
        \Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory $salesEventFactory,
        \Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface $placeReservationsForSalesEvent,
        \Ecombricks\StoreInventory\Model\InventorySales\StoreSalesChannelFactory $storeSalesChannelFactory,
        \Magento\InventorySales\Model\GetItemsToCancelFromOrderItem $getItemsToCancelFromOrderItem
    )
    {
        $this->priceIndexer = $priceIndexer;
        $this->salesEventFactory = $salesEventFactory;
        $this->placeReservationsForSalesEvent = $placeReservationsForSalesEvent;
        $this->storeSalesChannelFactory = $storeSalesChannelFactory;
        $this->getItemsToCancelFromOrderItem = $getItemsToCancelFromOrderItem;
    }

    /**
     * Around execute
     *
     * @param \Magento\InventorySales\Observer\CatalogInventory\CancelOrderItemObserver $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function aroundExecute(
        \Magento\InventorySales\Observer\CatalogInventory\CancelOrderItemObserver $subject,
        \Closure $proceed,
        \Magento\Framework\Event\Observer $observer
    ): void
    {
        $orderItem = $observer->getEvent()->getItem();
        $itemsToCancel = $this->getItemsToCancelFromOrderItem->execute($orderItem);
        if (empty($itemsToCancel)) {
            return;
        }
        $this->placeReservationsForSalesEvent->execute(
            $itemsToCancel,
            $this->storeSalesChannelFactory->createByStore($orderItem->getStore()),
            $this->salesEventFactory->create([
                'type' => \Magento\InventorySalesApi\Api\Data\SalesEventInterface::EVENT_ORDER_CANCELED,
                'objectType' => \Magento\InventorySalesApi\Api\Data\SalesEventInterface::OBJECT_TYPE_ORDER,
                'objectId' => (string) $orderItem->getOrderId(),
            ])
        );
        $this->priceIndexer->reindexRow($orderItem->getProductId());
    }

}
