<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Api\Data;

/**
 * Exceeded order limit day.
 * @api
 */
interface RestrictedDateInterface
{
    public const DAY = 'day';
    public const INTERVALS = 'intervals';

    /**
     * @param \Amasty\DeliveryDateManager\Api\Data\RestrictedTimeIntervalInterface $timeInterval
     * @return void
     */
    public function addInterval(
        \Amasty\DeliveryDateManager\Api\Data\RestrictedTimeIntervalInterface $timeInterval
    ): void;

    /**
     * Date in ISO format e.g. 1970-01-01
     * @return string
     */
    public function getDay(): string;

    /**
     * Get restricted time intervals.
     * null time intervals mean all day is restricted.
     *
     * @return \Amasty\DeliveryDateManager\Api\Data\RestrictedTimeIntervalInterface[]|null
     */
    public function getIntervals(): ?array;
}
