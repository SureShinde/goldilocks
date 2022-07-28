<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Controller\Deliverydate;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateOrderInterface;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\Edit\NotificationSender;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\OutputFormatter;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\Validator;
use Amasty\DeliveryDateManager\Model\Preprocessor\CompositePreprocessor;
use Amasty\DeliveryDateManager\Request\Validator\DDEditValidatorInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;

class Save implements HttpPostActionInterface
{
    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DDEditValidatorInterface
     */
    private $editValidator;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var NotificationSender
     */
    private $notificationSender;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Amasty\DeliveryDateManager\Model\DeliveryOrder\Save
     */
    private $saveCommand;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var OutputFormatter
     */
    private $outputFormatter;

    /**
     * @var CompositePreprocessor
     */
    private $dataPreprocessor;

    public function __construct(
        ResultFactory $resultFactory,
        \Amasty\DeliveryDateManager\Model\DeliveryOrder\Save $saveCommand,
        LoggerInterface $logger,
        DDEditValidatorInterface $editValidator,
        RequestInterface $request,
        NotificationSender $notificationSender,
        ManagerInterface $messageManager,
        Validator $validator,
        OutputFormatter $outputFormatter,
        CompositePreprocessor $dataPreprocessor
    ) {
        $this->resultFactory = $resultFactory;
        $this->saveCommand = $saveCommand;
        $this->logger = $logger;
        $this->editValidator = $editValidator;
        $this->request = $request;
        $this->notificationSender = $notificationSender;
        $this->messageManager = $messageManager;
        $this->validator = $validator;
        $this->outputFormatter = $outputFormatter;
        $this->dataPreprocessor = $dataPreprocessor;
    }

    /**
     * Save Delivery Date Action for Customer
     * Can be used by Guest @see \Amasty\DeliveryDateManager\Controller\Guest\Save
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $validationResult = $this->editValidator->validateRequest($this->request);
        if (!$validationResult->isSuccess()) {
            return $validationResult->getResult();
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $order = $validationResult->getOrder();

        try {
            $data = $this->request->getPostValue();
            if (is_array($data) && !empty($data)) {
                $deliveryDate = $validationResult->getDeliveryDate();
                $this->dataPreprocessor->process($data);
                $deliveryDate->addData($data);

                $this->validator->validateDeliveryOrder($deliveryDate);
                $this->saveCommand->execute($deliveryDate);
                $this->sendNotification($deliveryDate, $order);
                $this->messageManager->addSuccessMessage(__('Delivery Date has been successfully saved'));
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect->setPath('*/*/edit', ['order_id' => $order->getId()]);

            return $resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong while saving data.'));
            $this->logger->critical($e);
        }

        $this->setRedirectUrl($resultRedirect, (int)$order->getId());

        return $resultRedirect;
    }

    /**
     * @param Redirect $result
     * @param int $orderId
     * @return void
     */
    protected function setRedirectUrl(Redirect $result, int $orderId): void
    {
        $result->setPath('sales/order/view', ['order_id' => $orderId]);
    }

    /**
     * @param DeliveryDateOrderInterface $deliveryDate
     * @param OrderInterface $order
     */
    private function sendNotification(DeliveryDateOrderInterface $deliveryDate, OrderInterface $order): void
    {
        $formattedDate = $this->outputFormatter->getFormattedDateFromDeliveryOrder($deliveryDate);
        $deliveryDate->setDate($formattedDate);
        $deliveryDate = $this->outputFormatter->formatOutputTimes($deliveryDate);
        $this->notificationSender->sendNotification($deliveryDate, $order, NotificationSender::ADMIN_RECIPIENT_KEY);
    }
}
