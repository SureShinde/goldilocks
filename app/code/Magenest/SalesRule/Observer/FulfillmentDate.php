<?php

namespace Magenest\SalesRule\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magenest\SalesRule\Model\Rule\Condition\FulfillmentDate as FulfillmentDateCondition;

class FulfillmentDate implements ObserverInterface
{
    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $additional = $observer->getAdditional();
        $conditions = (array)$additional->getConditions();

        $conditions = array_merge_recursive($conditions, [
            $this->getDeliveryDateCondition()
        ]);

        $additional->setConditions($conditions);
    }

    /**
     * @return array
     */
    private function getDeliveryDateCondition(): array
    {
        return [
            'label' => __('Delivery Date'),
            'value' => FulfillmentDateCondition::class
        ];
    }
}
