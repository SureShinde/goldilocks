<?php

declare(strict_types=1);

namespace Amasty\PreOrderRelease\Model\Source;

use Magento\Sales\Model\Config\Source\Order\Status;

class OrderStatus extends Status
{
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
