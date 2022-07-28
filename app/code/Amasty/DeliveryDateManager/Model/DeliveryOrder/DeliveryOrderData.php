<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryOrder;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateOrderInterface;
use Amasty\DeliveryDateManager\Model\AbstractTypifiedModel;

class DeliveryOrderData extends AbstractTypifiedModel implements DeliverydateOrderInterface
{
    protected function _construct()
    {
        $this->_init(\Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryDateOrder::class);
    }

    /**
     * @return int
     */
    public function getDeliverydateId(): int
    {
        return (int)$this->_getData(DeliveryDateOrderInterface::DELIVERYDATE_ID);
    }

    /**
     * @param int $deliverydateId
     *
     * @return void
     */
    public function setDeliverydateId(int $deliverydateId): void
    {
        $this->setData(DeliveryDateOrderInterface::DELIVERYDATE_ID, $deliverydateId);
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return (int)$this->_getData(DeliveryDateOrderInterface::ORDER_ID);
    }

    /**
     * @param int $orderId
     *
     * @return void
     */
    public function setOrderId(int $orderId): void
    {
        $this->setData(DeliveryDateOrderInterface::ORDER_ID, $orderId);
    }

    /**
     * @return int
     */
    public function getCounterId(): int
    {
        return (int)$this->_getData(DeliveryDateOrderInterface::COUNTER_ID);
    }

    /**
     * @param int $counterId
     *
     * @return void
     */
    public function setCounterId(int $counterId): void
    {
        $this->setData(DeliveryDateOrderInterface::COUNTER_ID, $counterId);
    }

    /**
     * @return string
     */
    public function getIncrementId(): string
    {
        return $this->_getData(DeliveryDateOrderInterface::INCREMENT_ID);
    }

    /**
     * @param string $incrementId
     *
     * @return void
     */
    public function setIncrementId(string $incrementId): void
    {
        $this->setData(DeliveryDateOrderInterface::INCREMENT_ID, $incrementId);
    }

    /**
     * @return string|null
     */
    public function getDate(): ?string
    {
        return $this->_getData(DeliveryDateOrderInterface::DATE);
    }

    /**
     * @param string|null $date
     *
     * @return void
     */
    public function setDate(?string $date): void
    {
        $this->setData(DeliveryDateOrderInterface::DATE, $date);
    }

    /**
     * @return int|null
     */
    public function getTimeFrom(): ?int
    {
        $data = $this->_getData(DeliveryDateOrderInterface::TIME_FROM);
        if ($data === null) {
            return null;
        }

        return (int)$data;
    }

    /**
     * @param int|null $timeFrom
     *
     * @return void
     */
    public function setTimeFrom(?int $timeFrom): void
    {
        $this->setData(DeliveryDateOrderInterface::TIME_FROM, $timeFrom);
    }

    /**
     * @return int|null
     */
    public function getTimeTo(): ?int
    {
        $data = $this->_getData(DeliveryDateOrderInterface::TIME_TO);
        if ($data === null) {
            return null;
        }

        return (int)$data;
    }

    /**
     * @param int|null $timeTo
     *
     * @return void
     */
    public function setTimeTo(?int $timeTo): void
    {
        $this->setData(DeliveryDateOrderInterface::TIME_TO, $timeTo);
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->_getData(DeliveryDateOrderInterface::COMMENT);
    }

    /**
     * @param string|null $comment
     *
     * @return void
     */
    public function setComment(?string $comment): void
    {
        $this->setData(DeliveryDateOrderInterface::COMMENT, $comment);
    }

    /**
     * @return int
     */
    public function getReminder(): int
    {
        return (int)$this->_getData(DeliveryDateOrderInterface::REMINDER);
    }

    /**
     * @param int $reminder
     *
     * @return void
     */
    public function setReminder(int $reminder): void
    {
        $this->setData(DeliveryDateOrderInterface::REMINDER, $reminder);
    }

    /**
     * @return int|null
     */
    public function getTimeIntervalId(): ?int
    {
        $data = $this->_getData(DeliveryDateOrderInterface::TIME_INTERVAL_ID);
        if ($data === null) {
            return null;
        }

        return (int)$data;
    }

    /**
     * @param int|null $timeIntervalId
     *
     * @return void
     */
    public function setTimeIntervalId(?int $timeIntervalId): void
    {
        $this->setData(DeliveryDateOrderInterface::TIME_INTERVAL_ID, $timeIntervalId);
    }
}
