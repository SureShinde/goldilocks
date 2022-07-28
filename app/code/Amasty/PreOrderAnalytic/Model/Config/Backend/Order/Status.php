<?php

declare(strict_types=1);

namespace Amasty\PreOrderAnalytic\Model\Config\Backend\Order;

use Magento\Framework\App\Config\Value as ConfigValue;
use Magento\Sales\Model\Order\Config as OrderConfig;

class Status extends ConfigValue
{
    /**
     * @return Status
     */
    protected function _afterLoad()
    {
        if ($this->getValue() === null) {
            $this->setValue($this->getDefaultValue());
        }

        return parent::_afterLoad();
    }

    private function getDefaultValue(): string
    {
        /** @var OrderConfig $orderConfig */
        if ($orderConfig = $this->getData('order_config')) {
            $statuses = $this->getData('default_states')
                ? $orderConfig->getStateStatuses($this->getData('default_states'))
                : $orderConfig->getStatuses();

            $statuses = array_keys($statuses);

            $value = implode(',', $statuses);
        } else {
            $value = '';
        }

        return $value;
    }
}
