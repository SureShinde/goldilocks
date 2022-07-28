<?php

namespace Magenest\GoogleTagManager\Block\Adminhtml\Form\Field\Product;

class Attributes extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    const MAIN_COLUMN_ID  = 'attribute';
    const ALIAS_COLUMN_ID = 'alias';

    /**
     * @var \Magenest\GoogleTagManager\Block\Adminhtml\Form\Field\Product\Attributes\Code
     */
    private $attributeRenderer;

    /**
     * Prepare to render
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            self::MAIN_COLUMN_ID,
            ['label' => \__('Attribute'), 'renderer' => $this->getRenderer()]
        );

        $this->addColumn(self::ALIAS_COLUMN_ID, ['label' => \__('Alias')]);
        $this->_addAfter = false;
    }

    /**
     * Prepare existing row data object
     *
     * @param \Magento\Framework\DataObject $row
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];

        $fieldId = \sprintf('option_%s', $this->getRenderer()->calcOptionHash($row->getData(self::MAIN_COLUMN_ID)));

        $optionExtraAttr[$fieldId] = 'selected="selected"';

        $row->setData('option_extra_attrs', $optionExtraAttr);
    }

    /**
     * Retrieve attribute column renderer
     *
     * @return \Magento\Framework\View\Element\BlockInterface|Attributes\Code
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getRenderer()
    {
        if (!$this->attributeRenderer) {
            $this->attributeRenderer = $this->getLayout()->createBlock(
                \Magenest\GoogleTagManager\Block\Adminhtml\Form\Field\Product\Attributes\Code::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );

            $this->attributeRenderer->setClass(\sprintf('%s_select', self::MAIN_COLUMN_ID));
        }

        return $this->attributeRenderer;
    }
}
