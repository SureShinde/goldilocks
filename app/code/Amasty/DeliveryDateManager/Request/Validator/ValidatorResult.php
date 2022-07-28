<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Request\Validator;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateOrderInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Sales\Api\Data\OrderInterface;

class ValidatorResult
{
    private $isSuccess = false;

    /**
     * @var ResultInterface
     */
    private $result;

    /**
     * @var DeliveryDateOrderInterface
     */
    private $deliveryDate;

    /**
     * @var OrderInterface
     */
    private $order;

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    /**
     * @param bool $isSuccess
     */
    public function setIsSuccess(bool $isSuccess): void
    {
        $this->isSuccess = $isSuccess;
    }

    /**
     * @return DeliveryDateOrderInterface
     */
    public function getDeliveryDate(): DeliveryDateOrderInterface
    {
        return $this->deliveryDate;
    }

    /**
     * @param DeliveryDateOrderInterface $deliveryDateOrder
     */
    public function setDeliveryDate(DeliveryDateOrderInterface $deliveryDateOrder): void
    {
        $this->deliveryDate = $deliveryDateOrder;
    }

    /**
     * @return OrderInterface
     */
    public function getOrder(): OrderInterface
    {
        return $this->order;
    }

    /**
     * @param OrderInterface $order
     */
    public function setOrder(OrderInterface $order): void
    {
        $this->order = $order;
    }

    /**
     * @return ResultInterface
     */
    public function getResult(): ResultInterface
    {
        return $this->result;
    }

    /**
     * @param ResultInterface $result
     */
    public function setResult(ResultInterface $result): void
    {
        $this->result = $result;
    }
}
