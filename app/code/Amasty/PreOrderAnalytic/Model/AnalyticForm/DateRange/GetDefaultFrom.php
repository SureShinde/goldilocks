<?php

declare(strict_types=1);

namespace Amasty\PreOrderAnalytic\Model\AnalyticForm\DateRange;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class GetDefaultFrom
{
    /**
     * @var TimezoneInterface
     */
    private $timezone;

    public function __construct(TimezoneInterface $timezone)
    {
        $this->timezone = $timezone;
    }

    public function execute(): string
    {
        return $this->timezone->formatDateTime(
            '-30 days',
            null,
            null,
            null,
            null,
            'yyyy-MM-dd'
        );
    }
}
