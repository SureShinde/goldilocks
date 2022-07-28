<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Block\InventoryShippingAdminUi\Adminhtml\Order\View;

/**
 * Order ship button plugin
 */
class ShipButton extends \Ecombricks\Common\Plugin\Block\Backend\Widget\Container
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
     * @param \Ecombricks\Common\Object\WrapperFactory $wrapperFactory
     * @param \Ecombricks\StoreInventory\Model\InventoryShippingAdminUi\IsStoreInMultiSourceMode $isStoreInMultiSourceMode
     * @param \Ecombricks\StoreInventory\Model\InventoryShippingAdminUi\IsOrderSourceManageable $isOrderSourceManageable
     * @return void
     */
    public function __construct(
        \Ecombricks\Common\Object\WrapperFactory $wrapperFactory,
        \Ecombricks\StoreInventory\Model\InventoryShippingAdminUi\IsStoreInMultiSourceMode $isStoreInMultiSourceMode,
        \Ecombricks\StoreInventory\Model\InventoryShippingAdminUi\IsOrderSourceManageable $isOrderSourceManageable
    )
    {
        parent::__construct($wrapperFactory);
        $this->isStoreInMultiSourceMode = $isStoreInMultiSourceMode;
        $this->isOrderSourceManageable = $isOrderSourceManageable;
    }

    /**
     * Prepare layout
     *
     * @return $this
     */
    protected function prepareLayout()
    {
        parent::prepareLayout();
        $subject = $this->getSubject();
        $registry = $this->getSubjectPropertyValue('registry');
        $buttonList = $this->getSubjectPropertyValue('buttonList');
        $order = $registry->registry('current_order');
        $storeId = (int) $order->getStore()->getId();
        if ($this->isStoreInMultiSourceMode->execute($storeId) && $this->isOrderSourceManageable->execute($order)) {
            $buttonList->update('order_ship', 'onclick', 'setLocation(\''.$subject->getSourceSelectionUrl().'\')');
        }
        return $this;
    }

}
