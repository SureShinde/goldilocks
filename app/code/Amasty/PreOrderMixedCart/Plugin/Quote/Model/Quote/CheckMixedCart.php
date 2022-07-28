<?php

declare(strict_types=1);

namespace Amasty\PreOrderMixedCart\Plugin\Quote\Model\Quote;

use Amasty\Preorder\Model\Quote\Item\GetPreorderInformation;
use Amasty\PreOrderMixedCart\Model\IsMixedCartAllowed;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item as QuoteItem;

class CheckMixedCart
{
    /**
     * @var GetPreorderInformation
     */
    private $getPreorderInformation;

    /**
     * @var IsMixedCartAllowed
     */
    private $isMixedCartAllowed;

    public function __construct(GetPreorderInformation $getPreorderInformation, IsMixedCartAllowed $isMixedCartAllowed)
    {
        $this->getPreorderInformation = $getPreorderInformation;
        $this->isMixedCartAllowed = $isMixedCartAllowed;
    }

    /**
     * @param Quote $quote
     * @param QuoteItem|string $quoteItem
     * @return QuoteItem|string
     * @throws LocalizedException
     */
    public function afterAddProduct(Quote $quote, $quoteItem)
    {
        if (!is_string($quoteItem) && !$this->isMixedCartAllowed->execute()) {
            $visibleItems = $quote->getAllVisibleItems();
            array_pop($visibleItems);
            if ($visibleItems) {
                $lastVisibleItem = end($visibleItems);
                $isCartPreorder = $this->getPreorderInformation->execute($lastVisibleItem)->isPreorder();
                $isNewItemPreorder = $this->getPreorderInformation->execute($quoteItem)->isPreorder();
                if ($isCartPreorder !== $isNewItemPreorder) {
                    $quote->deleteItem($quoteItem);
                    throw new LocalizedException(__(
                        'Preorder and regular products are not allowed to be added to the same shopping cart.'
                    ));
                }
            }
        }

        return $quoteItem;
    }
}
