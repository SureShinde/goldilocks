<?php

namespace Magenest\GoogleTagManager\Block;

class Gtm extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magenest\GoogleTagManager\Helper\Data
     */
    private $gtmHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magenest\GoogleTagManager\Helper\Data $gtmHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magenest\GoogleTagManager\Helper\Data $gtmHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->gtmHelper = $gtmHelper;
    }

    public function getAccountId()
    {
        return $this->gtmHelper->getAccountId();
    }

    protected function _toHtml()
    {
        if (!$this->gtmHelper->isEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }
}
