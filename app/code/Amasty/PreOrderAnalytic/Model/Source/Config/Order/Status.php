<?php

declare(strict_types=1);

namespace Amasty\PreOrderAnalytic\Model\Source\Config\Order;

use Magento\Sales\Model\Config\Source\Order\Status as OrderStatus;
use Magento\Sales\Model\Order;

class Status extends OrderStatus
{
    /**
     * @var string[]
     */
    protected $_stateStatuses = [
        Order::STATE_NEW,
        Order::STATE_PROCESSING,
        Order::STATE_COMPLETE,
        Order::STATE_CLOSED,
        Order::STATE_CANCELED,
        Order::STATE_HOLDED,
        Order::STATE_PENDING_PAYMENT,
        Order::STATE_PAYMENT_REVIEW
    ];

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = parent::toOptionArray();
        array_shift($options);

        return $options;
    }
}
