<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Plugin\Sales\Model\Order\Email\Sender\OrderSender;

use Amasty\DeliveryDateManager\Model\DeliveryOrder\LoaderExtensions;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

class LoadExtensionAttributes
{
    /**
     * @var LoaderExtensions
     */
    private $loaderExtensions;

    public function __construct(
        LoaderExtensions $loaderExtensions
    ) {
        $this->loaderExtensions = $loaderExtensions;
    }

    /**
     * @param OrderSender $subject
     * @param Order $order
     * @param bool $forceSyncMode
     */
    public function beforeSend(OrderSender $subject, Order $order, $forceSyncMode = false)
    {
        $this->loaderExtensions->loadDeliveryDateExtensionAttributes($order);
    }
}
