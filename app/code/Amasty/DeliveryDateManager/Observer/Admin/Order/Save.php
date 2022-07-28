<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Observer\Admin\Order;

use Amasty\DeliveryDateManager\Model\DeliveryOrder\DeliveryOrderDataFactory;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\Get;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\Save as DeliveryOrderSave;
use Amasty\DeliveryDateManager\Model\Preprocessor\CompositePreprocessor;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;

class Save implements ObserverInterface
{
    /**
     * @var Get
     */
    private $deliveryOrderGetter;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var DeliveryOrderSave
     */
    private $deliveryOrderSaver;

    /**
     * @var CompositePreprocessor
     */
    private $dataPreprocessor;

    /**
     * @var DeliveryOrderDataFactory
     */
    private $deliveryOrderFactory;

    public function __construct(
        Get $deliveryOrderGetter,
        RequestInterface $request,
        DeliveryOrderSave $deliveryOrderSaver,
        CompositePreprocessor $dataPreprocessor,
        DeliveryOrderDataFactory $deliveryOrderFactory
    ) {
        $this->deliveryOrderGetter = $deliveryOrderGetter;
        $this->request = $request;
        $this->deliveryOrderSaver = $deliveryOrderSaver;
        $this->dataPreprocessor = $dataPreprocessor;
        $this->deliveryOrderFactory = $deliveryOrderFactory;
    }

    /**
     * Event name 'sales_order_save_after'
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer): void
    {
        /** @var Order $order */
        $order = $observer->getOrder();

        $data = $this->request->getParam('amdeliverydate');
        if (is_array($data) && !empty($data)) {
            $orderId = (int)$order->getEntityId();
            try {
                $deliveryOrder = $this->deliveryOrderGetter->getByOrderId($orderId);
            } catch (NoSuchEntityException $e) {
                $deliveryOrder = $this->deliveryOrderFactory->create();
            }

            $this->dataPreprocessor->process($data);
            $deliveryOrder->addData($data);
            $deliveryOrder->setOrderId($orderId);
            $deliveryOrder->setIncrementId($order->getIncrementId());

            $this->deliveryOrderSaver->execute($deliveryOrder);
        }
    }
}
