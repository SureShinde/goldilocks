<?php

namespace Magenest\AbandonedCart\Ui\Component\Listing\Column\Template;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magenest\AbandonedCart\Model\AbandonedCart;

class Status extends AbstractSource implements SourceInterface, OptionSourceInterface
{
    /**
     * Retrieve option array
     * @return string[]
     */
    public function getOptionArray()
    {
        return [
            AbandonedCart::STATUS_ABANDONED => __('ABANDONED'),
            AbandonedCart::STATUS_NOT_ABANDONED => __('NOT ABANDONED'),
            AbandonedCart::STATUS_RECOVERED => __('RECOVERED'),
            AbandonedCart::STATUS_CONVERTED => __('COMPLETED')
        ];
    }

    /**
     * Retrieve option array with empty value
     * @return string[]
     */
    public function getAllOptions()
    {
        $result = [];

        foreach ($this->getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }
}
