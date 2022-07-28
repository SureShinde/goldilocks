<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\Config\Source;

use Magento\Cms\Model\Config\Source\Block;

class CmsBlocks extends Block
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = parent::toOptionArray();
        array_unshift($options, ['value' => '', 'label' => __('Please select a static block.')]);

        return $options;
    }
}
