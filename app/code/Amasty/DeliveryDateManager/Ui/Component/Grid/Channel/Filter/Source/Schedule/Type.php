<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Ui\Component\Grid\Channel\Filter\Source\Schedule;

use Magento\Framework\Data\OptionSourceInterface;
use Amasty\DeliveryDateManager\Model\DateSchedule\Type as TypeConverter;

class Type implements OptionSourceInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        if ($this->options === null) {
            $this->options = [
                [
                    'value' => TypeConverter::STRICT,
                    'label' => __('Specific Date Range')
                ],
                [
                    'value' => TypeConverter::DAY_OF_YEAR,
                    'label' => __('Days of Year')
                ],
                [
                    'value' => TypeConverter::DAY_OF_MONTH,
                    'label' => __('Days of Month')
                ],
                [
                    'value' => TypeConverter::DAY_OF_WEEK,
                    'label' => __('Days of Week')
                ]
            ];
        }

        return $this->options;
    }
}
