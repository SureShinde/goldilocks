<?php

namespace Magenest\GoogleTagManager\Observer;

class RemoveQuoteItemObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magenest\GoogleTagManager\Helper\CatalogSession
     */
    private $sessionHelper;

    public function __construct(
        \Magenest\GoogleTagManager\Helper\CatalogSession $sessionHelper
    ) {
        $this->sessionHelper = $sessionHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Quote\Model\Quote\Item $item */
        $item = $observer->getData('quote_item');

        $this->sessionHelper->removeItem($item, $item->getQty());
    }
}
