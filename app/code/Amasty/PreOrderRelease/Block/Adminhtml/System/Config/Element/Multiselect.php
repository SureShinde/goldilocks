<?php

declare(strict_types=1);

namespace Amasty\PreOrderRelease\Block\Adminhtml\System\Config\Element;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement as AbstractElement;

class Multiselect extends Field
{
    public const DEFAULT_SIZE = 10;

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $countOptions = count($element->getValues());
        $size = $countOptions ? min($countOptions, self::DEFAULT_SIZE) : self::DEFAULT_SIZE;
        $element->setData('size', $size);

        return $element->getElementHtml();
    }
}
