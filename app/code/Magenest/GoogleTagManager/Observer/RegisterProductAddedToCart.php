<?php

namespace Magenest\GoogleTagManager\Observer;

class RegisterProductAddedToCart implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magenest\GoogleTagManager\Helper\Data
     */
    private $dataHelper;
    /**
     * @var \Magenest\GoogleTagManager\Model\CartState
     */
    private $cartState;

    public function __construct(
        \Magenest\GoogleTagManager\Helper\Data $dataHelper,
        \Magenest\GoogleTagManager\Model\CartState $cartState
    ) {
        $this->dataHelper = $dataHelper;
        $this->cartState = $cartState;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->dataHelper->isEnabled()) {
            return;
        }

        /** @var \Magento\Quote\Model\Quote\Item $item */
        $item = $observer->getData('quote_item');

        $this->cartState->registerQuoteItem($item);
    }
}
