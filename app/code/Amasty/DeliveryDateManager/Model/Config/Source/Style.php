<?php

namespace Amasty\DeliveryDateManager\Model\Config\Source;

class Style implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 'as_is',
                'label' => __('As is')
            ],
            [
                'value' => 'notice',
                'label' => __('Magento Notice')
            ],
        ];
    }
}
