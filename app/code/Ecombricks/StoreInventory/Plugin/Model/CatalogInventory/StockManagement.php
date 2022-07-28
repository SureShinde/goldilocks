<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Model\CatalogInventory;

/**
 * Stock management plugin
 */
class StockManagement
{

    /**
     * Get SKUs by product IDs
     *
     * @var \Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface
     */
    protected $getSkusByProductIds;

    /**
     * Sales event factory
     *
     * @var \Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory
     */
    protected $salesEventFactory;

    /**
     * Store sales channel factory
     *
     * @var \Ecombricks\StoreInventory\Model\InventorySales\StoreSalesChannelFactory
     */
    protected $storeSalesChannelFactory;

    /**
     * Items to sell factory
     *
     * @var \Magento\InventorySalesApi\Api\Data\ItemToSellInterfaceFactory
     */
    protected $itemsToSellFactory;

    /**
     * Place reservations for sales event
     *
     * @var \Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface
     */
    protected $placeReservationsForSalesEvent;

    /**
     * Is source item management allowed for product type
     *
     * @var \Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface
     */
    protected $isSourceItemManagementAllowedForProductType;

    /**
     * Get product types by SKUs
     *
     * @var \Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface
     */
    protected $getProductTypesBySkus;

    /**
     * Constructor
     *
     * @param \Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface $getSkusByProductIds
     * @param \Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory $salesEventFactory
     * @param \Ecombricks\StoreInventory\Model\InventorySales\StoreSalesChannelFactory $storeSalesChannelFactory
     * @param \Magento\InventorySalesApi\Api\Data\ItemToSellInterfaceFactory $itemsToSellFactory
     * @param \Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface $placeReservationsForSalesEvent
     * @param \Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
     * @param \Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface $getProductTypesBySkus
     * @return void
     */
    public function __construct(
        \Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface $getSkusByProductIds,
        \Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory $salesEventFactory,
        \Ecombricks\StoreInventory\Model\InventorySales\StoreSalesChannelFactory $storeSalesChannelFactory,
        \Magento\InventorySalesApi\Api\Data\ItemToSellInterfaceFactory $itemsToSellFactory,
        \Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface $placeReservationsForSalesEvent,
        \Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType,
        \Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface $getProductTypesBySkus
    )
    {
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->salesEventFactory = $salesEventFactory;
        $this->storeSalesChannelFactory = $storeSalesChannelFactory;
        $this->itemsToSellFactory = $itemsToSellFactory;
        $this->placeReservationsForSalesEvent = $placeReservationsForSalesEvent;
        $this->isSourceItemManagementAllowedForProductType = $isSourceItemManagementAllowedForProductType;
        $this->getProductTypesBySkus = $getProductTypesBySkus;
    }

    /**
     * Around back item qty
     *
     * @param \Magento\CatalogInventory\Model\StockManagement $subject
     * @param \Closure $proceed
     * @param int $productId
     * @param float $qty
     * @param int|null $storeId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundBackItemQty(
        \Magento\CatalogInventory\Model\StockManagement $subject,
        \Closure $proceed,
        $productId,
        $qty,
        $storeId = null
    ): bool
    {
        if (null === $storeId) {
            throw new \Magento\Framework\Exception\LocalizedException(__('$storeId is required'));
        }
        try {
            $sku = $this->getSkusByProductIds->execute([$productId])[$productId];
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            return true;
        }
        $productType = $this->getProductTypesBySkus->execute([$sku])[$sku];
        if (!$this->isSourceItemManagementAllowedForProductType->execute($productType)) {
            return true;
        }
        $this->placeReservationsForSalesEvent->execute(
            [$this->itemsToSellFactory->create(['sku' => $sku, 'qty' => (float) $qty])],
            $this->storeSalesChannelFactory->createByStore($storeId),
            $this->salesEventFactory->create([
                'type' => 'back_item_qty',
                'objectType' => 'legacy_stock_management_api',
                'objectId' => 'none',
            ])
        );
        return true;
    }

    /**
     * Around revert products sale
     *
     * @param \Magento\CatalogInventory\Model\StockManagement $subject
     * @param \Closure $proceed
     * @param float[] $items
     * @param int|null $storeId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundRevertProductsSale(
        \Magento\CatalogInventory\Model\StockManagement $subject,
        \Closure $proceed,
        $items,
        $storeId = null
    )
    {
        if (empty($items)) {
            return true;
        }
        if (null === $storeId) {
            throw new \Magento\Framework\Exception\LocalizedException(__('$storeId parameter is required'));
        }
        $skus = $this->getSkusByProductIds->execute(array_keys($items));
        $productTypes = $this->getProductTypesBySkus->execute(array_values($skus));
        $itemsToSell = [];
        foreach ($skus as $productId => $sku) {
            if (true === $this->isSourceItemManagementAllowedForProductType->execute($productTypes[$sku])) {
                $itemsToSell[] = $this->itemsToSellFactory->create([
                    'sku' => $sku,
                    'qty' => (float) $items[$productId]
                ]);
            }
        }
        $this->placeReservationsForSalesEvent->execute(
            $itemsToSell,
            $this->storeSalesChannelFactory->createByStore($storeId),
            $this->salesEventFactory->create([
                'type' => 'revert_products_sale',
                'objectType' => 'legacy_stock_management_api',
                'objectId' => 'none',
            ])
        );
        return true;
    }

}
