<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Api\Data;

/**
 * Exceeded order limit time interval.
 * From To in minutes starts at 00:00
 * e.g. 00:00 = 0; 2:00 = 120;
 * @api
 */
interface RestrictedTimeIntervalInterface
{
    public const KEY_FROM ='from';
    public const KEY_TO ='to';

    /**
     * Return Minutes
     * @return int
     */
    public function getFrom(): int;

    /**
     * Return Minutes
     * @return int
     */
    public function getTo(): int;
}
