<?php

namespace Magenest\GoogleTagManager\Block\Enterprise;

class GtmProxy extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magenest\GoogleTagManager\Model\ContextInfo\Generator
     */
    private $contextInfoGenerator;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var null|string
     */
    private $contextInfo;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magenest\GoogleTagManager\Model\ContextInfo\Generator $contextInfoGenerator
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magenest\GoogleTagManager\Model\ContextInfo\Generator $contextInfoGenerator,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->contextInfoGenerator = $contextInfoGenerator;
        $this->jsonHelper = $jsonHelper;
    }

    protected function _toHtml()
    {
        if (!$this->getContextInfo()) {
            return '';
        }

        return parent::_toHtml();
    }

    public function getContextInfo()
    {
        if ($this->contextInfo === null) {
            $this->contextInfo = [];

            /** @var \Magento\GoogleTagManager\Block\ListJson $gtm */
            $gtm = $this->getChildBlock('gtm');

            if ($gtm && $gtm->getListBlock()) {
                $this->contextInfo = $this->contextInfoGenerator->generate($gtm);
            }
        }

        return $this->contextInfo;
    }

    public function getContextInfoJson()
    {
        $escapedData = \array_map(function ($item) {
            return \array_map([$this, 'escapeJsQuote'], $item);
        }, $this->getContextInfo());

        return $this->jsonHelper->jsonEncode($escapedData);
    }
}
