<?php

declare(strict_types=1);

namespace Amasty\PreOrderMixedCart\Observer\Frontend\Quote\Item;

use Amasty\Preorder\Model\Quote\Item\IsPreorder;
use Amasty\PreOrderMixedCart\Model\IsMixedCartAllowed;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;

class CheckQuoteItemChange implements ObserverInterface
{
    /**
     * @var IsMixedCartAllowed
     */
    private $isMixedCartAllowed;

    /**
     * @var IsPreorder
     */
    private $isPreorder;

    public function __construct(IsPreorder $isPreorder, IsMixedCartAllowed $isMixedCartAllowed)
    {
        $this->isMixedCartAllowed = $isMixedCartAllowed;
        $this->isPreorder = $isPreorder;
    }

    public function execute(Observer $observer)
    {
        /** @var QuoteItem $quoteItem */
        $quoteItem = $observer->getEvent()->getData('item');
        if ($quoteItem
            && $quoteItem->getId()
            && !$this->isMixedCartAllowed->execute()
            && $quoteItem->getQuote()
            && count($quoteItem->getQuote()->getAllVisibleItems()) > 1
        ) {
            $isOrigItemPreorder = $this->isPreorder->execute(
                $quoteItem,
                (float) $quoteItem->getOrigData(QuoteItem::KEY_QTY)
            );
            $isNewItemPreorder = $this->isPreorder->execute(
                $quoteItem,
                (float) $quoteItem->getData(QuoteItem::KEY_QTY)
            );
            if ($isOrigItemPreorder !== $isNewItemPreorder) {
                $quoteItem->setHasError(true);
                $quoteItem->setMessage(
                    __('Preorder and regular products are not allowed to be added to the same shopping cart.')
                );
            }
        }
    }
}
