<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Api\Data;

interface DateScheduleInterface extends \Amasty\DeliveryDateManager\Api\LimitableDataInterface
{
    /**
     * Constants defined for keys of data array
     */
    public const SCHEDULE_ID = 'schedule_id';
    public const NAME = 'name';
    public const TYPE = 'type';
    public const FROM = 'from';
    public const TO = 'to';
    public const IS_AVAILABLE = 'is_available';

    /**
     * @return int
     */
    public function getScheduleId(): int;

    /**
     * @param int|null $scheduleId
     *
     * @return void
     */
    public function setScheduleId(?int $scheduleId): void;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     *
     * @return void
     */
    public function setName(string $name): void;

    /**
     * @return int
     */
    public function getType(): int;

    /**
     * @param int $type
     *
     * @return void
     */
    public function setType(int $type): void;

    /**
     * @return string
     */
    public function getFrom(): string;

    /**
     * @param string $from
     *
     * @return void
     */
    public function setFrom(string $from): void;

    /**
     * @return string
     */
    public function getTo(): string;

    /**
     * @param string $to
     *
     * @return void
     */
    public function setTo(string $to): void;

    /**
     * @return int
     */
    public function getIsAvailable(): int;

    /**
     * @param int $isAvailable
     *
     * @return void
     */
    public function setIsAvailable(int $isAvailable): void;
}
