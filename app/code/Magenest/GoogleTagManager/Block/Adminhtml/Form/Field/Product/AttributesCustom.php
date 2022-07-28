<?php

namespace Magenest\GoogleTagManager\Block\Adminhtml\Form\Field\Product;

class AttributesCustom extends Attributes
{
    protected function _prepareToRender()
    {
        $this->addColumn(self::MAIN_COLUMN_ID, ['label' => \__('Custom Attribute Key')]);
        $this->addColumn(self::ALIAS_COLUMN_ID, ['label' => \__('Alias')]);
        $this->_addAfter = false;
    }

    /**
     * Prepare existing row data object
     *
     * @param \Magento\Framework\DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row) // phpcs:ignore VCQP.CodeAnalysis.EmptyBlock.DetectedFunction
    {
    }
}
