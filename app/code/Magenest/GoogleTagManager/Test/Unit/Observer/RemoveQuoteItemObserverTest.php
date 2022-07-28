<?php

namespace Magenest\GoogleTagManager\Test\Unit\Observer;

use Magento\Quote\Model\Quote\Item as QuoteItem;

use Magenest\GoogleTagManager\Observer\RemoveQuoteItemObserver as Model;

use function Magenest\TestingTools\Functions\get;
use function Magenest\TestingTools\Functions\observe;
use function Magenest\TestingTools\Functions\v;
use function Magenest\TestingTools\Functions\mock;
use function Magenest\TestingTools\Functions\r;

class RemoveQuoteItemObserverTest extends \Magenest\TestingTools\Test\Unit\TestCase
{
    public function testExecuteShouldRegisterProductRemovalAndQuantity()
    {
        /** @var Model $model */
        $model = get([
            'sessionHelper..removeItem' => observe($removeProduct)
        ]);

        $quoteItem = mock(QuoteItem::class, [], [
            'getQty' => v('qty', 1)
        ]);

        $observer = get('observer:', [
            'getData' => r('quote_item', $quoteItem)
        ]);

        $model->execute($observer);

        $this->assertCalledOnceWith($removeProduct, [$quoteItem, v('qty')]);
    }
}
