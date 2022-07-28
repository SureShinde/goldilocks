<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Api\Data;

interface DeliveryDateOrderInterface
{
    /**
     * Constants defined for keys of data array
     */
    public const DELIVERYDATE_ID = 'deliverydate_id';
    public const COUNTER_ID = 'counter_id';
    public const ORDER_ID = 'order_id';
    public const INCREMENT_ID = 'increment_id';
    public const DATE = 'date';
    public const TIME_FROM = 'time_from';
    public const TIME_TO = 'time_to';
    public const COMMENT = 'comment';
    public const REMINDER = 'reminder';
    public const TIME_INTERVAL_ID = 'time_interval_id';

    public const REMINDER_WAIT_VALUE = 0;
    public const REMINDER_SENT_VALUE = 1;

    /**
     * @return int
     */
    public function getDeliverydateId(): int;

    /**
     * @param int $deliverydateId
     *
     * @return void
     */
    public function setDeliverydateId(int $deliverydateId): void;

    /**
     * @return int
     */
    public function getCounterId(): int;

    /**
     * @param int $counterId
     *
     * @return void
     */
    public function setCounterId(int $counterId): void;

    /**
     * @return int
     */
    public function getOrderId(): int;

    /**
     * @param int $orderId
     *
     * @return void
     */
    public function setOrderId(int $orderId): void;

    /**
     * @return string
     */
    public function getIncrementId(): string;

    /**
     * @param string $incrementId
     *
     * @return void
     */
    public function setIncrementId(string $incrementId): void;

    /**
     * @return string|null
     */
    public function getDate(): ?string;

    /**
     * @param string|null $date
     *
     * @return void
     */
    public function setDate(?string $date): void;

    /**
     * @return int|null
     */
    public function getTimeFrom(): ?int;

    /**
     * @param int|null $timeFrom
     *
     * @return void
     */
    public function setTimeFrom(?int $timeFrom): void;

    /**
     * @return int|null
     */
    public function getTimeTo(): ?int;

    /**
     * @param int|null $timeTo
     *
     * @return void
     */
    public function setTimeTo(?int $timeTo): void;

    /**
     * @return string|null
     */
    public function getComment(): ?string;

    /**
     * @param string|null $comment
     *
     * @return void
     */
    public function setComment(?string $comment): void;

    /**
     * @return int
     */
    public function getReminder(): int;

    /**
     * @param int $reminder
     *
     * @return void
     */
    public function setReminder(int $reminder): void;

    /**
     * @return int|null
     */
    public function getTimeIntervalId(): ?int;

    /**
     * @param int|null $timeIntervalId
     *
     * @return void
     */
    public function setTimeIntervalId(?int $timeIntervalId): void;
}
