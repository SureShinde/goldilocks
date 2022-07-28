<?php

namespace Magenest\AbandonedCart\Ui\Component\Listing\Column\ABTestCampaigns;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Framework\Data\OptionSourceInterface;

class Status extends AbstractSource implements SourceInterface, OptionSourceInterface
{

    /**
     * Retrieve option array
     * @return string[]
     */
    public function getOptionArray()
    {
        return [
            0 => __('Inactive'),
            1 => __('Active')
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
