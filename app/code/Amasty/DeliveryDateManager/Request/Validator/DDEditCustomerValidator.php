<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Request\Validator;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateOrderInterface;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\Get;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\Edit\Validator\EditableOnFront;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Amasty\DeliveryDateManager\Request\Validator\ValidatorResultFactory;
use Amasty\DeliveryDateManager\Request\Validator\ValidatorResult;
use Magento\Sales\Controller\AbstractController\OrderViewAuthorization;
use Magento\Sales\Model\OrderRepository;

class DDEditCustomerValidator implements DDEditValidatorInterface
{
    /**
     * @var \Amasty\DeliveryDateManager\Request\Validator\ValidatorResultFactory
     */
    private $validatorResultFactory;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var Get
     */
    private $getDeliveryDate;

    /**
     * @var OrderViewAuthorization
     */
    private $orderAuthorization;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var EditableOnFront
     */
    private $editableOnFront;

    public function __construct(
        ValidatorResultFactory $validatorResultFactory,
        ResultFactory $resultFactory,
        Get $getDeliveryDate,
        OrderViewAuthorization $orderAuthorization,
        OrderRepository $orderRepository,
        EditableOnFront $editableOnFront
    ) {
        $this->validatorResultFactory = $validatorResultFactory;
        $this->resultFactory = $resultFactory;
        $this->getDeliveryDate = $getDeliveryDate;
        $this->orderAuthorization = $orderAuthorization;
        $this->orderRepository = $orderRepository;
        $this->editableOnFront = $editableOnFront;
    }

    public function validateRequest(RequestInterface $request): ValidatorResult
    {
        /** @var ValidatorResult $validatorResult */
        $validatorResult = $this->validatorResultFactory->create();
        $orderId = (int)$request->getParam('order_id');
        if (!$orderId) {
            $validatorResult->setIsSuccess(false);
            /** @var \Magento\Framework\Controller\Result\Forward $forward */
            $forward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $forward->forward('defaultNoRoute');
            $validatorResult->setResult($forward);

            return $validatorResult;
        }
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $validatorResult->setIsSuccess(false);
        $validatorResult->setResult($resultRedirect);
        try {
            $deliverydate = $this->getDeliveryDate->getByOrderId($orderId);
            $order = $this->orderRepository->get($orderId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);

            return $validatorResult;
        }
        if (!$this->orderAuthorization->canView($order)) {
            $resultRedirect->setPath('sales/order/history');

            return $validatorResult;
        }
        if (!$this->editableOnFront->validate($deliverydate)) {
            $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);

            return $validatorResult;
        }

        $validatorResult->setIsSuccess(true);
        $validatorResult->setOrder($order);
        $validatorResult->setDeliveryDate($deliverydate);

        return $validatorResult;
    }
}
