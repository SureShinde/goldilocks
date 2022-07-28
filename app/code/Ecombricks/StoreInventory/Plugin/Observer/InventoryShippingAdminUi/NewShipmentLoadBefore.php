<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Observer\InventoryShippingAdminUi;

/**
 * New shipment load before observer plugin
 */
class NewShipmentLoadBefore
{

    /**
     * Order repository
     *
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * Is store in multi source mode
     *
     * @var \Ecombricks\StoreInventory\Model\InventoryShippingAdminUi\IsStoreInMultiSourceMode
     */
    protected $isStoreInMultiSourceMode;

    /**
     * Redirect
     *
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * Is order source manageable
     *
     * @var \Ecombricks\StoreInventory\Model\InventoryShippingAdminUi\IsOrderSourceManageable
     */
    protected $isOrderSourceManageable;

    /**
     * Constructor
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Ecombricks\StoreInventory\Model\InventoryShippingAdminUi\IsStoreInMultiSourceMode $isStoreInMultiSourceMode
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Ecombricks\StoreInventory\Model\InventoryShippingAdminUi\IsOrderSourceManageable $isOrderSourceManageable
     * @return void
     */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Ecombricks\StoreInventory\Model\InventoryShippingAdminUi\IsStoreInMultiSourceMode $isStoreInMultiSourceMode,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Ecombricks\StoreInventory\Model\InventoryShippingAdminUi\IsOrderSourceManageable $isOrderSourceManageable
    )
    {
        $this->orderRepository = $orderRepository;
        $this->isStoreInMultiSourceMode = $isStoreInMultiSourceMode;
        $this->redirect = $redirect;
        $this->isOrderSourceManageable = $isOrderSourceManageable;
    }

    /**
     * Around execute
     *
     * @param \Magento\InventoryShippingAdminUi\Observer\NewShipmentLoadBefore $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function aroundExecute(
        \Magento\InventoryShippingAdminUi\Observer\NewShipmentLoadBefore $subject,
        \Closure $proceed,
        \Magento\Framework\Event\Observer $observer
    )
    {
        $event = $observer->getEvent();
        $request = $event->getRequest();
        $controller = $event->getControllerAction();
        if (
            !empty($request->getParam('items')) &&
            !empty($request->getParam('sourceCode'))
        ) {
            return;
        }
        try {
            $orderId = $request->getParam('order_id');
            $order = $this->orderRepository->get($orderId);
            if (!$this->isOrderSourceManageable->execute($order)) {
                return;
            }
            $storeId = (int) $order->getStore()->getId();
            if ($this->isStoreInMultiSourceMode->execute($storeId)) {
                $this->redirect->redirect($controller->getResponse(), 'inventoryshipping/SourceSelection/index', ['order_id' => $orderId]);
            }
        } catch (\Magento\Framework\Exception\InputException | \Magento\Framework\Exception\NoSuchEntityException $exception) {
            $this->redirect->redirect($controller->getResponse(), 'sales/order/index');
        }
        return;
    }

}
