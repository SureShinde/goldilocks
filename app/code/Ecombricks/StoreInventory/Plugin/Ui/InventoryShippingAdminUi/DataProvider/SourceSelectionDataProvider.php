<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Ui\InventoryShippingAdminUi\DataProvider;

/**
 * Source selection data provider plugin
 */
class SourceSelectionDataProvider extends \Ecombricks\Common\Plugin\Plugin
{

    /**
     * Get stock ID by store
     *
     * @var \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface
     */
    protected $getStockIdByStore;

    /**
     * Constructor
     *
     * @param \Ecombricks\Common\Object\WrapperFactory $wrapperFactory
     * @param \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
     * @param \Magento\InventorySalesApi\Model\GetSkuFromOrderItemInterface $getSkuFromOrderItem
     * @return void
     */
    public function __construct(
        \Ecombricks\Common\Object\WrapperFactory $wrapperFactory,
        \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
    )
    {
        parent::__construct($wrapperFactory);
        $this->getStockIdByStore = $getStockIdByStore;
    }

    /**
     * Around get data
     *
     * @param \Magento\InventoryShippingAdminUi\Ui\DataProvider\SourceSelectionDataProvider $subject
     * @param \Closure $proceed
     * @return array
     */
    public function aroundGetData(
        \Magento\InventoryShippingAdminUi\Ui\DataProvider\SourceSelectionDataProvider $subject,
        \Closure $proceed
    ): array
    {
        $this->setSubject($subject);
        $request = $this->getSubjectPropertyValue('request');
        $orderRepository = $this->getSubjectPropertyValue('orderRepository');
        $getSkuFromOrderItem = $this->getSubjectPropertyValue('getSkuFromOrderItem');
        $data = [];
        $orderId = (int) $request->getParam('order_id');
        $order = $orderRepository->get($orderId);
        $store = $order->getStore();
        $stockId = $this->getStockIdByStore->execute($store);
        foreach ($order->getAllItems() as $orderItem) {
            if ($orderItem->getIsVirtual() || $orderItem->getLockedDoShip() || $orderItem->getHasChildren()) {
                continue;
            }
            $containerOrderItem = $orderItem->isDummy(true) ? $orderItem->getParentItem() : $orderItem;
            $sku = $getSkuFromOrderItem->execute($containerOrderItem);
            $qty = $this->invokeSubjectMethod('castQty', $containerOrderItem, $containerOrderItem->getSimpleQtyToShip());
            $data[$orderId]['items'][] = [
                'orderItemId' => $containerOrderItem->getId(),
                'sku' => $sku,
                'product' => $this->invokeSubjectMethod('getProductName', $orderItem),
                'qtyToShip' => $qty,
                'sources' => $this->invokeSubjectMethod('getSources', $orderId, $sku, $qty),
                'isManageStock' => $this->invokeSubjectMethod('isManageStock', $sku, $stockId),
            ];
        }
        $data[$orderId]['websiteId'] = (int) $store->getWebsiteId();
        $data[$orderId]['storeId'] = (int) $store->getId();
        $data[$orderId]['order_id'] = $orderId;
        foreach ($this->getSubjectPropertyValue('sources') as $code => $name) {
            $data[$orderId]['sourceCodes'][] = ['value' => $code, 'label' => $name];
        }
        return $data;
    }

}
