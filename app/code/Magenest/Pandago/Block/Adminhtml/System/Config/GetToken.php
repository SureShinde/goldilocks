<?php

namespace Magenest\Pandago\Block\Adminhtml\System\Config;

/**
 * Block GetToken
 */
class GetToken extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @return $this|GetToken
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('system/config/get_token.phtml');
        }

        return $this;
    }

    /**
     * Before rendering html, but after trying to load cache
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->addData(
            [
                'add_class' => ("btn-success"),
                'button_label' => __("Test connection"),
                'html_id' => "get_access_token_button",
            ]
        );
        $element->setComment(
            '<strong style="color:red">Warning</strong>: Please save the configuration before test connection'
        );

        return $this->_toHtml();
    }
}
