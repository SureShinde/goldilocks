<?php

declare(strict_types=1);

namespace Amasty\PreOrderMixedCart\Observer\Frontend\Quote\Item;

use Amasty\Preorder\Model\Quote\Item\GetPreorderInformation;
use Amasty\PreOrderMixedCart\Model\IsMixedCartAllowed;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;

class CheckMixedCarts implements ObserverInterface
{
    public const MIXED_CART_CODE = 999;

    /**
     * @var IsMixedCartAllowed
     */
    private $isMixedCartAllowed;

    /**
     * @var GetPreorderInformation
     */
    private $getPreorderInformation;

    /**
     * @var bool
     */
    private $isAnyItemOnPreorder = false;

    /**
     * @var bool
     */
    private $isAnyItemInStock = false;

    public function __construct(GetPreorderInformation $getPreorderInformation, IsMixedCartAllowed $isMixedCartAllowed)
    {
        $this->isMixedCartAllowed = $isMixedCartAllowed;
        $this->getPreorderInformation = $getPreorderInformation;
    }

    public function execute(Observer $observer)
    {
        /** @var QuoteItem $quoteItem */
        $quoteItem = $observer->getEvent()->getData('item');
        if ($quoteItem
            && $quoteItem->getId()
            && $quoteItem->getParentItemId() === null
            && !$this->isMixedCartAllowed->execute()
            && $quoteItem->getQuote()
            && count($quoteItem->getQuote()->getAllVisibleItems()) > 1
        ) {
            if ($this->getPreorderInformation->execute($quoteItem)->isPreorder()) {
                $this->isAnyItemOnPreorder = true;
            } else {
                $this->isAnyItemInStock = true;
            }

            if ($this->isAnyItemInStock && $this->isAnyItemOnPreorder) {
                $quoteItem->getQuote()->addErrorInfo(
                    'preorder',
                    'preorder',
                    self::MIXED_CART_CODE,
                    __('Sorry, you can’t order preorder and regular products together.
                    Please, rearrange your shopping cart.')
                );
            }
        }
    }
}
