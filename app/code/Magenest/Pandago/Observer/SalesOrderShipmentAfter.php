<?php

namespace Magenest\Pandago\Observer;

use Magenest\Pandago\Model\Api;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\ShipmentRepository;

class SalesOrderShipmentAfter implements ObserverInterface
{
    /**
     * @var Api
     */
    private Api $api;
    /**
     * @var ShipmentRepository
     */
    private ShipmentRepository $shipmentRepository;

    /**
     * SalesOrderShipmentAfter constructor.
     * @param Api $api
     * @param ShipmentRepository $shipmentRepository
     */
    public function __construct(
        Api $api,
        ShipmentRepository $shipmentRepository
    ) {
        $this->api = $api;
        $this->shipmentRepository = $shipmentRepository;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Http_Client_Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Shipment $shipment */
        $shipment = $observer->getEvent()->getShipment();
        /** @var \Magento\Sales\Model\Order $order */
        $order = $shipment->getOrder();
        if ($order->getShippingMethod() === 'pandago_pandago' && empty($shipment->getTracks())) {
            $shipment = $this->api->createOrder($shipment);
            $this->shipmentRepository->save($shipment);
        }
    }
}
