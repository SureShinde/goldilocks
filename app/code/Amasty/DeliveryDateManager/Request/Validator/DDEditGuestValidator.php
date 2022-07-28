<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Request\Validator;

use Amasty\DeliveryDateManager\Model\DeliveryOrder\Get;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\Edit\Validator\EditableOnFront;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Amasty\DeliveryDateManager\Request\Validator\ValidatorResultFactory;

class DDEditGuestValidator implements DDEditValidatorInterface
{
    /**
     * @var ValidatorResultFactory
     */
    private $validatorResultFactory;

    /**
     * @var Get
     */
    private $getDeliveryDate;

    /**
     * @var \Magento\Sales\Helper\Guest
     */
    private $guestHelper;

    /**
     * @var EditableOnFront
     */
    private $editableOnFront;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    public function __construct(
        ValidatorResultFactory $validatorResultFactory,
        ResultFactory $resultFactory,
        Get $getDeliveryDate,
        \Magento\Sales\Helper\Guest $guestHelper,
        EditableOnFront $editableOnFront,
        Registry $registry
    ) {
        $this->validatorResultFactory = $validatorResultFactory;
        $this->resultFactory = $resultFactory;
        $this->getDeliveryDate = $getDeliveryDate;
        $this->guestHelper = $guestHelper;
        $this->editableOnFront = $editableOnFront;
        $this->registry = $registry;
    }

    /**
     * @param RequestInterface $request
     * @return ValidatorResult
     */
    public function validateRequest(RequestInterface $request): ValidatorResult
    {
        /** @var ValidatorResult $validatorResult */
        $validatorResult = $this->validatorResultFactory->create();

        /* load order to register by params from session for guest */
        $result = $this->guestHelper->loadValidOrder($request);
        if ($result instanceof \Magento\Framework\Controller\ResultInterface) {
            $validatorResult->setIsSuccess(false);
            $validatorResult->setResult($result);

            return $validatorResult;
        }
        // This is only way to get valid order from guest helper
        $order = $this->registry->registry('current_order');
        try {
            $deliverydate = $this->getDeliveryDate->getByOrderId((int)$order->getId());
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $deliverydate = null;
        }

        if (!$deliverydate || !$this->editableOnFront->validate($deliverydate)) {
            /** @var Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('sales/guest/view');
            $validatorResult->setIsSuccess(false);
            $validatorResult->setResult($resultRedirect);

            return $validatorResult;
        }

        $validatorResult->setIsSuccess(true);
        $validatorResult->setOrder($order);
        $validatorResult->setDeliveryDate($deliverydate);

        return $validatorResult;
    }
}
