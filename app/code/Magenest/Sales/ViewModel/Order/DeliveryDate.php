<?php

namespace Magenest\Sales\ViewModel\Order;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateOrderInterface;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\Get;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\OutputFormatter;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class DeliveryDate implements ArgumentInterface
{
    private Get $deliveryOrderGet;
    private OutputFormatter $outputFormatter;
    private $deliveryDateOrder;

    /**
     * @param Get $deliveryOrderGet
     * @param OutputFormatter $outputFormatter
     */
    public function __construct(
        Get             $deliveryOrderGet,
        OutputFormatter $outputFormatter
    ) {
        $this->deliveryOrderGet = $deliveryOrderGet;
        $this->outputFormatter = $outputFormatter;
    }

    /**
     * @param $order
     * @return false|string
     */
    public function getDeliveryDate($order)
    {
        $deliveryDateOrder = $this->getDeliveryDateData($order->getId());
        if ($deliveryDateOrder->getDate()) {
            return date("Y-m-d", strtotime($deliveryDateOrder->getDate()));
        }
        return '';
    }

    /**
     * @param int $orderId
     * @return DeliveryDateOrderInterface
     */
    public function getDeliveryDateData(int $orderId): DeliveryDateOrderInterface
    {
        if (!$this->deliveryDateOrder) {
            $this->deliveryDateOrder = $this->deliveryOrderGet->getByOrderId($orderId);
        }
        return $this->deliveryDateOrder;
    }

    /**
     * @param $order
     * @return string
     */
    public function getDeliveryTime($order): string
    {
        $deliveryDateOrder = $this->getDeliveryDateData($order->getId());
        return $this->outputFormatter->getTimeLabelFromDeliveryOrder($deliveryDateOrder);
    }
}
