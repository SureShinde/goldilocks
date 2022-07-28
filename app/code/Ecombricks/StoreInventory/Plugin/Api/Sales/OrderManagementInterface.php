<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Api\Sales;

/**
 * Order management interface plugin
 */
class OrderManagementInterface
{

    /**
     * Place reservations for sales event
     *
     * @var \Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface
     */
    protected $placeReservationsForSalesEvent;

    /**
     * Get SKUs by product IDs
     *
     * @var \Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface
     */
    protected $getSkusByProductIds;

    /**
     * Store sales channel factory
     *
     * @var \Ecombricks\StoreInventory\Model\InventorySales\StoreSalesChannelFactory
     */
    protected $storeSalesChannelFactory;

    /**
     * Sales event factory
     *
     * @var \Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory
     */
    protected $salesEventFactory;

    /**
     * Items to sell factory
     *
     * @var \Magento\InventorySalesApi\Api\Data\ItemToSellInterfaceFactory
     */
    protected $itemsToSellFactory;

    /**
     * Check items quantity
     *
     * @var \Magento\InventorySales\Model\CheckItemsQuantity
     */
    protected $checkItemsQuantity;

    /**
     * Get stock ID by store
     *
     * @var \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface
     */
    protected $getStockIdByStore;

    /**
     * Get product types by SKUs
     *
     * @var \Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface
     */
    protected $getProductTypesBySkus;

    /**
     * Is source item management allowed for product type
     *
     * @var \Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface
     */
    protected $isSourceItemManagementAllowedForProductType;

    /**
     * Constructor
     *
     * @param \Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface $placeReservationsForSalesEvent
     * @param \Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface $getSkusByProductIds
     * @param \Ecombricks\StoreInventory\Model\InventorySales\StoreSalesChannelFactory $storeSalesChannelFactory
     * @param \Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory $salesEventFactory
     * @param \Magento\InventorySalesApi\Api\Data\ItemToSellInterfaceFactory $itemsToSellFactory
     * @param \Magento\InventorySales\Model\CheckItemsQuantity $checkItemsQuantity
     * @param \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
     * @param \Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface $getProductTypesBySkus
     * @param \Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
     * @return void
     */
    public function __construct(
        \Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface $placeReservationsForSalesEvent,
        \Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface $getSkusByProductIds,
        \Ecombricks\StoreInventory\Model\InventorySales\StoreSalesChannelFactory $storeSalesChannelFactory,
        \Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory $salesEventFactory,
        \Magento\InventorySalesApi\Api\Data\ItemToSellInterfaceFactory $itemsToSellFactory,
        \Magento\InventorySales\Model\CheckItemsQuantity $checkItemsQuantity,
        \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore,
        \Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface $getProductTypesBySkus,
        \Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
    )
    {
        $this->placeReservationsForSalesEvent = $placeReservationsForSalesEvent;
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->storeSalesChannelFactory = $storeSalesChannelFactory;
        $this->salesEventFactory = $salesEventFactory;
        $this->itemsToSellFactory = $itemsToSellFactory;
        $this->checkItemsQuantity = $checkItemsQuantity;
        $this->getStockIdByStore = $getStockIdByStore;
        $this->getProductTypesBySkus = $getProductTypesBySkus;
        $this->isSourceItemManagementAllowedForProductType = $isSourceItemManagementAllowedForProductType;
    }

    /**
     * Get qtys
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return array
     */
    protected function getQtys(\Magento\Sales\Api\Data\OrderInterface $order): array
    {
        $qtys = [];
        foreach ($order->getItems() as $item) {
            $productId = $item->getProductId();
            if (!isset($qtys[$productId])) {
                $qtys[$productId] = 0;
            }
            $qtys[$productId] += $item->getQtyOrdered();
        }
        return $qtys;
    }

    /**
     * After place
     *
     * @param \Magento\Sales\Api\OrderManagementInterface $subject
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function afterPlace(
        \Magento\Sales\Api\OrderManagementInterface $subject,
        \Magento\Sales\Api\Data\OrderInterface $order
    ) : \Magento\Sales\Api\Data\OrderInterface
    {
        $qtys = $this->getQtys($order);
        $qtysBySku = $itemsToSell = [];
        $skus = $this->getSkusByProductIds->execute(array_keys($qtys));
        $productTypes = $this->getProductTypesBySkus->execute($skus);
        foreach ($skus as $productId => $sku) {
            if (false === $this->isSourceItemManagementAllowedForProductType->execute($productTypes[$sku])) {
                continue;
            }
            $qtysBySku[$sku] = (float) $qtys[$productId];
            $itemsToSell[] = $this->itemsToSellFactory->create([
                'sku' => $sku,
                'qty' => -(float) $qtys[$productId],
            ]);
        }
        $store = $order->getStore();
        $this->checkItemsQuantity->execute($qtysBySku, $this->getStockIdByStore->execute($store));
        $this->placeReservationsForSalesEvent->execute(
            $itemsToSell,
            $this->storeSalesChannelFactory->createByStore($store),
            $this->salesEventFactory->create([
                'type' => \Magento\InventorySalesApi\Api\Data\SalesEventInterface::EVENT_ORDER_PLACED,
                'objectType' => \Magento\InventorySalesApi\Api\Data\SalesEventInterface::OBJECT_TYPE_ORDER,
                'objectId' => (string) $order->getEntityId(),
            ])
        );
        return $order;
    }

}
