<?php


namespace Magenest\AbandonedCart\Model\Config\Source;

class ActiveInactive implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Value which equal Active for ActiveInactive dropdown.
     */
    const ACTIVE_VALUE = 1;

    /**
     * Value which equal Inactive for ActiveInactive dropdown.
     */
    const INACTIVE_VALUE = 0;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::ACTIVE_VALUE, 'label' => __('Active')],
            ['value' => self::INACTIVE_VALUE, 'label' => __('Inactive')],
        ];
    }
}
