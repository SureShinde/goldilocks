<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DateSchedule\Type;

use Amasty\DeliveryDateManager\Model\DateSchedule\Type;
use Amasty\DeliveryDateManager\Model\DeliveryDate\DateFormatProvider;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Date conversion to output according to schedule type
 */
class ConvertToOutput
{
    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var DateFormatProvider
     */
    private $dateFormatProvider;

    public function __construct(
        TimezoneInterface $localeDate,
        DateFormatProvider $dateFormatProvider
    ) {
        $this->localeDate = $localeDate;
        $this->dateFormatProvider = $dateFormatProvider;
    }

    /**
     * @param int $type
     * @param string $date
     * @return string
     */
    public function execute(int $type, string $date): string
    {
        switch ($type) {
            case Type::STRICT:
            case Type::DAY_OF_YEAR:
                if ($type === Type::DAY_OF_YEAR) {
                    $format = $this->dateFormatProvider->getDateFormatWithoutYear();
                } else {
                    $format = $this->dateFormatProvider->getDateFormat();
                }

                $date = $this->localeDate->formatDateTime(
                    $date,
                    \IntlDateFormatter::SHORT,
                    \IntlDateFormatter::NONE,
                    null,
                    $this->localeDate->getDefaultTimezone(),
                    $format
                );
                break;
            case Type::DAY_OF_MONTH:
                $date = date('d', strtotime($date));
                break;
            case Type::DAY_OF_WEEK:
                $date = date('D', strtotime($date));
                break;
        }

        return $date;
    }
}
