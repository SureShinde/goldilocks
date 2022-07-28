<?php

namespace Magenest\GoogleTagManager\Block\Adminhtml\Form\Field;

class Checkoutsteps extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var null|\Magenest\GoogleTagManager\Block\Adminhtml\Form\Field\Pages
     */
    private $pageRenderer;

    private function getPageRenderer()
    {
        if (!$this->pageRenderer) {
            $this->pageRenderer = $this->getLayout()->createBlock(
                \Magenest\GoogleTagManager\Block\Adminhtml\Form\Field\Pages::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->pageRenderer->setClass('pages_select');
        }

        return $this->pageRenderer;
    }

    protected function _prepareToRender()
    {
        $this->addColumn(
            'checkout_page',
            ['label' => \__('Checkout Page'), 'renderer' => $this->getPageRenderer()]
        );

        $this->addColumn('step_number', ['label' => \__('Step Number')]);

        $this->_addAfter = false;
        $this->_addButtonLabel = \__('Add');
    }

    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionHash = $this->getPageRenderer()->calcOptionHash(
            $row->getData('checkout_page')
        );

        $row->setData('option_extra_attrs', [
            \sprintf('option_%s', $optionHash) => 'selected="selected"',
        ]);
    }
}
