<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryDate;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Wrapper for timezone get date format
 */
class DateFormatProvider
{
    /**
     * Detect year with delimiter
     */
    public const REGEX_CUT_YEAR = '/(\/|-)(y{1,})$/i';

    public const FORMAT_MAP = [
        'dd'   => 'd',
        'd'    => 'j',
        'MM'   => 'm',
        'M'    => 'n',
        'yyyy' => 'Y',
        'yy'   => 'y',
        'y'   => 'Y',
    ];

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    public function __construct(TimezoneInterface $timezone)
    {
        $this->timezone = $timezone;
    }

    /**
     * @return string
     */
    public function getDateFormat(): string
    {
        return $this->timezone->getDateFormatWithLongYear();
    }

    /**
     * Convert date format of IntlDateFormatter to date format of DateTime
     *
     * @param string $value
     *
     * @return string
     */
    public function convert(string $value): string
    {
        foreach (static::FORMAT_MAP as $search => $replace) {
            $value = preg_replace('/(^|[^%])' . $search . '/', '$1' . $replace, $value);
        }

        return $value;
    }

    /**
     * @return string
     */
    public function getDateFormatWithoutYear(): string
    {
        return preg_replace(
            self::REGEX_CUT_YEAR,
            '',
            $this->getDateFormat()
        );
    }
}
