<?php

declare(strict_types=1);

namespace Amasty\PreOrderAnalytic\Model\Date;

use DateTimeZone;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Format
{
    const DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    public function __construct(TimezoneInterface $localeDate)
    {
        $this->localeDate = $localeDate;
    }

    public function execute(string $date, int $hour = 0, int $minute = 0, int $second = 0): string
    {
        $date = $this->localeDate->date($date, null, false, false);
        $date->setTime($hour, $minute, $second);
        $date->setTimezone(new DateTimeZone('UTC'));

        return $date->format(self::DATE_FORMAT);
    }
}
