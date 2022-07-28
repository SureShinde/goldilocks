<?php

namespace Amasty\Storelocator\Model\Config\Source;

class TimeFormat implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => '0',
                'label' => __('24h'),
            ],
            [
                'value' => '1',
                'label' => __('12h'),
            ]
        ];
    }
}
