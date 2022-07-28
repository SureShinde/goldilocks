<?php

namespace Magenest\ServicesUi\Model\Cron\Options;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Cron\Model\Schedule;

class Status implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => Schedule::STATUS_PENDING, 'label' => __('pending')],
            ['value' => Schedule::STATUS_RUNNING, 'label' => __('running')],
            ['value' => Schedule::STATUS_SUCCESS, 'label' => __('success')],
            ['value' => Schedule::STATUS_MISSED, 'label' => __('missed')],
            ['value' => Schedule::STATUS_ERROR, 'label' => __('error')]
        ];
    }
}

