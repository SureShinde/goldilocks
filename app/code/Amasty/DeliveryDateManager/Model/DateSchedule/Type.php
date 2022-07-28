<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DateSchedule;

/**
 * Date conversion according to schedule type
 */
class Type
{
    /**
     * Date Schedule types
     */
    // Date Range
    public const STRICT = 0;
    // Date without Year
    public const DAY_OF_YEAR = 1;
    // From 1 To 31
    public const DAY_OF_MONTH = 2;
    // ex. Sun, Mon, Tue
    public const DAY_OF_WEEK = 3;

    /**
     * Sorted type iterate
     */
    public const ITERATOR = [
        self::STRICT,
        self::DAY_OF_YEAR,
        self::DAY_OF_MONTH,
        self::DAY_OF_WEEK,
    ];

    /**
     * @param int $type
     * @param int $timestamp
     *
     * @return int
     */
    public function convertToComparable(int $type, int $timestamp): int
    {
        switch ($type) {
            case self::DAY_OF_YEAR:
                // 0 through 365
                return (int)date('z', $timestamp);
            case self::DAY_OF_MONTH:
                // 1 to 31
                return (int)date('j', $timestamp);
            case self::DAY_OF_WEEK:
                // 0 (for Sunday) through 6 (for Saturday)
                return (int)date('w', $timestamp);
        }

        return $timestamp;
    }
}
