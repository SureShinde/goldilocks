<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Ui\Component\Form\Channel\Modal\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ScheduleSourceProvider implements OptionSourceInterface
{
    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'Mon', 'label' => __('Monday')],
            ['value' => 'Tue', 'label' => __('Tuesday')],
            ['value' => 'Wed', 'label' => __('Wednesday')],
            ['value' => 'Thu', 'label' => __('Thursday')],
            ['value' => 'Fri', 'label' => __('Friday')],
            ['value' => 'Sat', 'label' => __('Saturday')],
            ['value' => 'Sun', 'label' => __('Sunday')]
        ];
    }
}
