<?php

namespace Magenest\Order\Block\Adminhtml\Order\Create;

use Magenest\Order\Helper\Data;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Session\Quote;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate;
use Magento\Sales\Model\AdminOrder\Create;

class Source extends AbstractCreate
{
    private Data $helper;

    /**
     * @param Context $context
     * @param Quote $sessionQuote
     * @param Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context                $context,
        Quote                  $sessionQuote,
        Create                 $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        Data                   $helper,
        array                  $data = []
    ) {
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $data);
        $this->helper = $helper;
    }

    /**
     * @return array
     */
    public function getSourceField(): array
    {
        return $this->helper->getSourceField($this->_sessionQuote->getStore()->getId());
    }
}
