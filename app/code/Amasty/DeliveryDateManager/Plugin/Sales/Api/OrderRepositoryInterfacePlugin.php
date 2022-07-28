<?php

namespace Amasty\DeliveryDateManager\Plugin\Sales\Api;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderRepositoryInterfacePlugin
{
    /**
     * @var \Amasty\DeliveryDateManager\Model\DeliveryOrder\Save
     */
    private $saveDeliveryOrder;

    /**
     * @var \Amasty\DeliveryDateManager\Model\DeliveryOrder\LoaderExtensions
     */
    private $loaderExtensions;

    public function __construct(
        \Amasty\DeliveryDateManager\Model\DeliveryOrder\Save $saveDeliveryOrder,
        \Amasty\DeliveryDateManager\Model\DeliveryOrder\LoaderExtensions $loaderExtensions
    ) {
        $this->saveDeliveryOrder = $saveDeliveryOrder;
        $this->loaderExtensions = $loaderExtensions;
    }

    /**
     * Save Order Delivery Date extension attribute
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     *
     * @return OrderInterface
     */
    public function afterSave(OrderRepositoryInterface $subject, OrderInterface $order)
    {
        $orderAttributes = $order->getExtensionAttributes();
        if (!$orderAttributes || !$orderAttributes->getAmdeliverydate()) {
            return $order;
        }

        $deliveryOrder = $orderAttributes->getAmdeliverydate();
        $deliveryOrder->setOrderId((int)$order->getEntityId());
        $deliveryOrder->setIncrementId($order->getIncrementId());

        $this->saveDeliveryOrder->execute($deliveryOrder);

        return $order;
    }

    /**
     * @param OrderRepositoryInterface   $subject
     * @param OrderInterface    $order
     *
     * @return OrderInterface
     */
    public function afterGet(OrderRepositoryInterface $subject, OrderInterface $order)
    {
        $this->loaderExtensions->loadDeliveryDateExtensionAttributes($order);

        return $order;
    }

    /**
     * @param OrderRepositoryInterface               $subject
     * @param \Magento\Sales\Api\Data\OrderSearchResultInterface    $orderCollection
     *
     * @return \Magento\Sales\Api\Data\OrderSearchResultInterface
     */
    public function afterGetList(OrderRepositoryInterface $subject, OrderSearchResultInterface $orderCollection)
    {
        foreach ($orderCollection->getItems() as $order) {
            $this->loaderExtensions->loadDeliveryDateExtensionAttributes($order);
        }

        return $orderCollection;
    }
}
