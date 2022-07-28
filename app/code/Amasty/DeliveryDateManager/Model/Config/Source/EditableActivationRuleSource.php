<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\Config\Source;

class EditableActivationRuleSource implements \Magento\Framework\Data\OptionSourceInterface
{
    public const BOTH = 'both';
    public const ONE_OF = 'one_of';
    public const STATUS = 'status';
    public const DATE = 'date';

    /**
     * @return array[] Options
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::BOTH,
                'label' => __('Date and Order Status')
            ],
            [
                'value' => self::ONE_OF,
                'label' => __('Date or Order Status')
            ],
            [
                'value' => self::STATUS,
                'label' => __('Order Status')
            ],
            [
                'value' => self::DATE,
                'label' => __('Date')
            ],
        ];
    }
}
