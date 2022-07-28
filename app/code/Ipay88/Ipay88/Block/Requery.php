<?php
/**
 * @author iPay88 Inc. <support@ipay88.com.my>
 * @package iPay88\ThirdpartyIntegration\Magento
 * @Description: Using for Magento 2.x and Philippines Only.
 */

namespace Ipay88\Ipay88\Block;

class Requery extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context
    )
    {
        parent::__construct($context);
    }
    protected function _prepareLayout()
    {

    }

    public function getErrorDescription(){
        return $this->getData('iPay88ErrorDesc');
    }

    public function getRequeryMessage() {
        return $this->getData('requeryMessage');
    }
}