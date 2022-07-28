<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Controller\Adminhtml\Sales\Order;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateOrderInterface;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\DeliveryOrderData;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\DeliveryOrderDataFactory;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\Edit\NotificationSender;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\Get;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\OutputFormatter;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\Save as DeliveryOrderSave;
use Amasty\DeliveryDateManager\Model\Preprocessor\CompositePreprocessor;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

class Save extends Action implements HttpPostActionInterface
{
    /**
     * @var Get
     */
    private $deliveryOrderGetter;

    /**
     * @var DeliveryOrderSave
     */
    private $deliveryOrderSaver;

    /**
     * @var CompositePreprocessor
     */
    private $dataPreprocessor;

    /**
     * @var NotificationSender
     */
    private $notificationSender;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var DeliveryOrderDataFactory
     */
    private $deliveryOrderFactory;

    /**
     * @var OutputFormatter
     */
    private $outputFormatter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Context $context,
        Get $deliveryOrderGetter,
        DeliveryOrderSave $deliveryOrderSaver,
        CompositePreprocessor $dataPreprocessor,
        NotificationSender $notificationSender,
        OrderRepositoryInterface $orderRepository,
        DeliveryOrderDataFactory $deliveryOrderFactory,
        OutputFormatter $outputFormatter,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->deliveryOrderGetter = $deliveryOrderGetter;
        $this->deliveryOrderSaver = $deliveryOrderSaver;
        $this->dataPreprocessor = $dataPreprocessor;
        $this->notificationSender = $notificationSender;
        $this->orderRepository = $orderRepository;
        $this->deliveryOrderFactory = $deliveryOrderFactory;
        $this->outputFormatter = $outputFormatter;
        $this->logger = $logger;
    }

    /**
     * Save Delivery Date Action for Admin
     *
     * @throw LocalizedException
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        if ($data = $this->getRequest()->getPostValue()) {
            $orderId = (int)$this->getRequest()->getParam('order_id');

            try {
                if (is_array($data) && !empty($data)) {
                    try {
                        $deliveryOrder = $this->deliveryOrderGetter->getByOrderId($orderId);
                    } catch (NoSuchEntityException $e) {
                        $deliveryOrder = $this->deliveryOrderFactory->create();
                    }
                    $this->dataPreprocessor->process($data);
                    $deliveryOrder->setData($data);
                    $this->actualizeIntervalId($deliveryOrder);

                    $this->deliveryOrderSaver->execute($deliveryOrder);
                    if ($data['notify']) {
                        $this->sendNotification($deliveryOrder);
                    }
                    $this->messageManager->addSuccessMessage(__('Delivery Information has been successfully saved'));
                } else {
                    throw new LocalizedException(__('The wrong configuration is specified.'));
                }
                $this->_redirect('sales/order/view', ['order_id' => $orderId]);

                return;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_redirect('amasty_deliverydate/sales_order/edit', ['order_id' => $orderId]);

                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Something went wrong while saving data.'));
                $this->logger->critical($e);
                $this->_redirect('amasty_deliverydate/sales_order/edit', ['order_id' => $orderId]);

                return;
            }
        }
    }

    /**
     * Unset interval ID when admin changed to custom time interval
     *
     * @param DeliveryDateOrderInterface|DeliveryOrderData $deliveryOrder
     * @return void
     */
    private function actualizeIntervalId(DeliveryDateOrderInterface $deliveryOrder): void
    {
        if ($deliveryOrder->dataHasChangedFor(DeliveryDateOrderInterface::TIME_FROM)
            || $deliveryOrder->dataHasChangedFor(DeliveryDateOrderInterface::TIME_TO)
        ) {
            $deliveryOrder->setData(DeliveryDateOrderInterface::TIME_INTERVAL_ID, null);
        }
    }

    /**
     * @param DeliveryDateOrderInterface $deliveryDate
     */
    private function sendNotification(DeliveryDateOrderInterface $deliveryDate): void
    {
        $order = $this->orderRepository->get($deliveryDate->getOrderId());
        $formattedDate = $this->outputFormatter->getFormattedDateFromDeliveryOrder($deliveryDate);
        $deliveryDate->setDate($formattedDate);
        $deliveryDate = $this->outputFormatter->formatOutputTimes($deliveryDate);
        $this->notificationSender->sendNotification($deliveryDate, $order, NotificationSender::CUSTOMER_RECIPIENT_KEY);
    }
}
