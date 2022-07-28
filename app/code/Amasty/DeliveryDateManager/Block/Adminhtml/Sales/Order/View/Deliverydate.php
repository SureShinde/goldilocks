<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Block\Adminhtml\Sales\Order\View;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateOrderInterface;
use Amasty\DeliveryDateManager\Model\Config\Source\Show;
use Amasty\DeliveryDateManager\Model\ConfigProvider;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\Get;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\InfoOutput;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;

/**
 * Sales areas Delivery Date Information output
 * Output via Plugin @see \Amasty\DeliveryDateManager\Plugin\Sales\Block\Adminhtml\Order\View\Info\AddDeliveryInfoBlock
 */
class Deliverydate extends Template
{
    /**
     * @var InfoOutput
     */
    private $infoOutput;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Get
     */
    private $deliveryOrderGetter;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    public function __construct(
        Context $context,
        InfoOutput $infoOutput,
        ConfigProvider $configProvider,
        Get $deliveryOrderGetter,
        InvoiceRepositoryInterface $invoiceRepository,
        ShipmentRepositoryInterface $shipmentRepository,
        array $data = []
    ) {
        $this->infoOutput = $infoOutput;
        $this->configProvider = $configProvider;
        $this->deliveryOrderGetter = $deliveryOrderGetter;
        $this->invoiceRepository = $invoiceRepository;
        $this->shipmentRepository = $shipmentRepository;
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function isEnabledModule(): bool
    {
        return $this->configProvider->isEnabled();
    }

    /**
     * @return bool
     */
    public function isEditable(): bool
    {
        return $this->getSalesArea() === Show::ORDER_VIEW;
    }

    /**
     * @return array
     */
    public function getDeliveryDateFields(): array
    {
        $deliveryDate = $this->getDeliveryDate();
        if (!$deliveryDate) {
            return [];
        }

        return $this->infoOutput->getOutput($deliveryDate, $this->getSalesArea());
    }

    /**
     * @return DeliveryDateOrderInterface|null
     */
    public function getDeliveryDate(): ?DeliveryDateOrderInterface
    {
        $orderId = null;
        if ($this->getRequest()->getParam('order_id')) {
            $orderId = (int)$this->getRequest()->getParam('order_id');
        }

        if ($invoiceId = (int)$this->getRequest()->getParam('invoice_id')) {
            $invoice = $this->invoiceRepository->get($invoiceId);
            $orderId = (int)$invoice->getOrderId();
        }

        if ($shipmentId = (int)$this->getRequest()->getParam('shipment_id')) {
            $shipment = $this->shipmentRepository->get($shipmentId);
            $orderId = (int)$shipment->getOrderId();
        }

        if (!$orderId) {
            return null;
        }

        try {
            return $this->deliveryOrderGetter->getByOrderId($orderId);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * @return string
     */
    public function getEditUrl(): string
    {
        return $this->getUrl(
            'amasty_deliverydate/sales_order/edit',
            ['order_id' => $this->getRequest()->getParam('order_id')]
        );
    }

    /**
     * @return string
     */
    public function getSalesArea(): string
    {
        return (string)$this->getData('sales_area');
    }
}
