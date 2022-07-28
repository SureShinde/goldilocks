<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Block\Shipping\Adminhtml;

/**
 * Create shipping block plugin
 */
class Create
{

    /**
     * Is store in multi source mode
     *
     * @var \Ecombricks\StoreInventory\Model\InventoryShippingAdminUi\IsStoreInMultiSourceMode
     */
    protected $isStoreInMultiSourceMode;

    /**
     * Is order source manageable
     *
     * @var \Ecombricks\StoreInventory\Model\InventoryShippingAdminUi\IsOrderSourceManageable
     */
    protected $isOrderSourceManageable;

    /**
     * Constructor
     *
     * @param \Ecombricks\StoreInventory\Model\InventoryShippingAdminUi\IsStoreInMultiSourceMode $isStoreInMultiSourceMode
     * @param \Ecombricks\StoreInventory\Model\InventoryShippingAdminUi\IsOrderSourceManageable $isOrderSourceManageable
     * @return void
     */
    public function __construct(
        \Ecombricks\StoreInventory\Model\InventoryShippingAdminUi\IsStoreInMultiSourceMode $isStoreInMultiSourceMode,
        \Ecombricks\StoreInventory\Model\InventoryShippingAdminUi\IsOrderSourceManageable $isOrderSourceManageable
    )
    {
        $this->isStoreInMultiSourceMode = $isStoreInMultiSourceMode;
        $this->isOrderSourceManageable = $isOrderSourceManageable;
    }

    /**
     * After get back URL
     *
     * @param \Magento\Shipping\Block\Adminhtml\Create $subject
     * @param $result
     * @return string
     */
    public function afterGetBackUrl(\Magento\Shipping\Block\Adminhtml\Create $subject, $result)
    {
        $shipment = $subject->getShipment();
        if (empty($shipment) || !$this->isOrderSourceManageable->execute($shipment->getOrder())) {
            return $result;
        }
        $storeId = (int) $shipment->getOrder()->getStore()->getId();
        $orderId = (int) $shipment->getOrderId();
        if ($this->isStoreInMultiSourceMode->execute($storeId)) {
            return $subject->getUrl('inventoryshipping/SourceSelection/index', ['order_id' => $orderId]);
        }
        return $result;
    }

}
