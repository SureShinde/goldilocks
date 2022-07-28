<?php

namespace Amasty\Storelocator\Model\Config\Source;

class RadiusType implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 'select',
                'label' => __('Dropdown'),
            ],
            [
                'value' => 'range',
                'label' => __('Slider'),
            ]
        ];
    }
}
