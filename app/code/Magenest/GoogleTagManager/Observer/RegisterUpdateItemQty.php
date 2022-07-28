<?php

namespace Magenest\GoogleTagManager\Observer;

class RegisterUpdateItemQty implements \Magento\Framework\Event\ObserverInterface
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

        /** @var \Magento\Checkout\Model\Cart $cart */
        $cart = $observer->getData('cart');

        /** @var \Magento\Quote\Model\Quote\Interceptor $quote */
        $quote = $cart->getQuote();

        foreach ($observer->getData('info')->getData() as $itemId => $itemInfo) {
            $this->cartState->registerQuoteItem($quote->getItemById($itemId));
        }
    }
}
