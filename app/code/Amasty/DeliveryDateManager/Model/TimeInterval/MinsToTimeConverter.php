<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\TimeInterval;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class MinsToTimeConverter
{
    /** @var TimezoneInterface */
    protected $timezone;

    public function __construct(TimezoneInterface $timezone)
    {
        $this->timezone = $timezone;
    }

    /**
     * Convert minutes to time in local format
     *
     * @param int $mins
     * @return string
     */
    public function execute(int $mins): string
    {
        return $this->timezone->formatDateTime(
            $this->toSystemTime($mins),
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::SHORT,
            null,
            $this->timezone->getDefaultTimezone()
        );
    }

    /**
     * @param int $mins
     * @return string
     */
    public function toSystemTime(int $mins): string
    {
        $timestamp = $mins * 60;

        return date('H:i', $timestamp);
    }
}
