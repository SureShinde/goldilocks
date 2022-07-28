<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Api\Data;

interface TimeIntervalDateScheduleRelationInterface
{
    /**
     * Constants defined for keys of data array
     */
    public const RELATION_ID = 'relation_id';
    public const DATE_SCHEDULE_ID = 'date_schedule_id';
    public const TIME_INTERVAL_ID = 'time_interval_id';

    /**
     * @return int
     */
    public function getRelationId(): int;

    /**
     * @param int $relationId
     *
     * @return void
     */
    public function setRelationId(int $relationId): void;

    /**
     * @return int
     */
    public function getDateScheduleId(): int;

    /**
     * @param int $dateScheduleId
     *
     * @return void
     */
    public function setDateScheduleId(int $dateScheduleId): void;

    /**
     * @return int
     */
    public function getTimeIntervalId(): int;

    /**
     * @param int $timeIntervalId
     *
     * @return void
     */
    public function setTimeIntervalId(int $timeIntervalId): void;
}
