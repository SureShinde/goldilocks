<?php


namespace Magenest\LastName\Plugin\Checkout;


/**
 * Class LayoutProcessorPlugin
 * @package Magenest\LastName\Plugin\Checkout
 */
class LayoutProcessorPlugin
{
    protected $_helperLastName;

    /**
     * LayoutProcessorPlugin constructor.
     * @param \Magenest\LastName\Helper\Data $helperLastName
     */
    public function __construct(\Magenest\LastName\Helper\Data $helperLastName)
    {
        $this->_helperLastName = $helperLastName;
    }

    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array  $jsLayout
    ) {
        if(!$this->_helperLastName->isLastnameRequired()) {
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']['lastname']['visible'] = false;
        }

        return $jsLayout;
    }
}
