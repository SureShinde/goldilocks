<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryOrder;

use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;

class LoaderExtensions
{
    /**
     * @var Get
     */
    private $getDeliveryOrder;

    /**
     * @var OrderExtensionFactory
     */
    private $orderExtensionFactory;

    public function __construct(
        Get $getDeliveryOrder,
        OrderExtensionFactory $orderExtensionFactory
    ) {
        $this->getDeliveryOrder = $getDeliveryOrder;
        $this->orderExtensionFactory = $orderExtensionFactory;
    }

    /**
     * @param OrderInterface $order
     */
    public function loadDeliveryDateExtensionAttributes(OrderInterface $order): void
    {
        $extensionAttributes = $order->getExtensionAttributes();

        if ($extensionAttributes === null) {
            $extensionAttributes = $this->orderExtensionFactory->create();
        }
        if ($extensionAttributes->getAmdeliverydate() !== null) {
            // Delivery Date entity is already loaded; no actions required
            return;
        }
        try {
            $deliveryDate = $this->getDeliveryOrder->getByOrderId((int)$order->getEntityId());

            $extensionAttributes->setAmdeliverydate($deliveryDate);

            $order->setExtensionAttributes($extensionAttributes);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            // Delivery Date entity cannot be loaded for current order; no actions required
            return;
        }
    }
}
