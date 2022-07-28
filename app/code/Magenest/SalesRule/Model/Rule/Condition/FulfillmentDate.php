<?php

namespace Magenest\SalesRule\Model\Rule\Condition;

use Amasty\DeliveryDateManager\Model\DeliveryQuote\Get;
use Magento\Framework\Model\AbstractModel;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Context;

class FulfillmentDate extends AbstractCondition
{
    /**
     * @var Get
     */
    private $getDeliveryQuote;

    /**
     * @param Context $context
     * @param Get $getDeliveryQuote
     * @param array $data
     */
    public function __construct(
        Context $context,
        Get $getDeliveryQuote,
        array   $data = []
    ) {
        parent::__construct($context, $data);
        $this->getDeliveryQuote = $getDeliveryQuote;
    }

    /**
     * @return $this|FulfillmentDate
     */
    public function loadAttributeOptions()
    {
        $this->setAttributeOption([
            'delivery_date' => __('Delivery Date')
        ]);
        return $this;
    }

    /**
     * @return string
     */
    public function getInputType(): string
    {
        return 'date';
    }

    /**
     * @return string
     */
    public function getValueElementType(): string
    {
        return 'date';
    }

    /**
     * @return $this|FulfillmentDate
     */
    public function loadOperatorOptions(): FulfillmentDate
    {
        $operators = [
            '>=' => __('From'),
            '<=' => __('To')
        ];
        $this->setOperatorOption($operators);

        return $this;
    }

    /**
     * @return FulfillmentDate
     */
    public function getValueElement()
    {
        $element = parent::getValueElement();
        $element->setClass('hasDatepicker');
        $element->setExplicitApply(true);
        return $element;
    }

    /**
     * @param AbstractModel $model
     * @return bool
     */
    public function validate(AbstractModel $model)
    {
        $addressId = $model->getQuote()->getShippingAddress() ? $model->getQuote()->getShippingAddress()->getId() : '';
        if ($addressId) {
            $deliveryDateQuote = $this->getDeliveryQuote->getByAddressId($addressId);
            if ($deliveryDateQuote->getData('date')) {
                $deliveryDate = date('Y-m-d H:i:s', strtotime($deliveryDateQuote->getData('date')));
                $model->setData('delivery_date', $deliveryDate);
            }
        }
        return parent::validate($model);
    }
}
