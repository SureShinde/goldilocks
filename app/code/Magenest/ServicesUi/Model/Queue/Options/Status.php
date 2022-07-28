<?php

namespace Magenest\ServicesUi\Model\Queue\Options;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\MysqlMq\Model\QueueManagement;


class Status implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => QueueManagement::MESSAGE_STATUS_NEW, 'label' => __('new')],
            ['value' => QueueManagement::MESSAGE_STATUS_IN_PROGRESS, 'label' => __('in progress')],
            ['value' => QueueManagement::MESSAGE_STATUS_COMPLETE, 'label' => __('complete')],
            ['value' => QueueManagement::MESSAGE_STATUS_RETRY_REQUIRED, 'label' => __('retry required')],
            ['value' => QueueManagement::MESSAGE_STATUS_ERROR, 'label' => __('error')],
            ['value' => QueueManagement::MESSAGE_STATUS_TO_BE_DELETED, 'label' => __('deleted')]
        ];
    }
}

