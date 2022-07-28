<?php

declare(strict_types=1);

namespace Amasty\PreOrderAnalytic\Model\AnalyticForm\DateRange;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class GetDefaultTo
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
            'now',
            null,
            null,
            null,
            null,
            'yyyy-MM-dd'
        );
    }
}
