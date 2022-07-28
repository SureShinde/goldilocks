<?php

namespace Magenest\GoogleTagManager\Block\Adminhtml\Form\Field;

class Pages extends \Magento\Framework\View\Element\Html\Select
{
    public function getPages()
    {
        return [
            'cart' => 'Cart Page',
            'shipping' => 'Shipping Step',
            'payment' => 'Payment Step',
            'success' => 'Success Page',
        ];
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }

    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->getPages() as $id => $label) {
                $this->addOption($id, \addslashes($label));
            }
        }

        return parent::_toHtml();
    }
}
