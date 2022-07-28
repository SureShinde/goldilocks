<?php

declare(strict_types=1);

namespace Amasty\PreOrderMixedCart\Test\Unit\Observer\Frontend\Quote\Item;

use Amasty\Preorder\Model\Quote\Item\IsPreorder;
use Amasty\PreOrderMixedCart\Model\IsMixedCartAllowed;
use Amasty\PreOrderMixedCart\Observer\Frontend\Quote\Item\CheckMixedCarts;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use PHPUnit\Framework\TestCase;

class CheckMixedCartsTest extends TestCase
{
    /**
     * @covers CheckMixedCarts::execute
     *
     * @dataProvider executeDataProvider
     *
     * @param bool $isMixedCartAllowedValue
     * @param bool $ifMoreThanOneItems
     * @param bool $isOrigPreorder
     * @param bool $isNewPreorder
     * @param bool $expectedResult
     * @return void
     */
    public function testExecute(
        bool $isMixedCartAllowedValue,
        bool $ifMoreThanOneItems,
        bool $isOrigPreorder,
        bool $isNewPreorder,
        bool $expectedResult
    ): void {
        $isPreorderMock = $this->createMock(IsPreorder::class);
        $isPreorderMock->expects($this->any())->method('execute')->willReturnOnConsecutiveCalls(
            $isOrigPreorder,
            $isNewPreorder
        );
        $isMixedCartAllowed = $this->createMock(IsMixedCartAllowed::class);
        $isMixedCartAllowed->expects($this->any())->method('execute')->willReturn($isMixedCartAllowedValue);
        $model = new CheckMixedCarts($isPreorderMock, $isMixedCartAllowed);

        $observer = $this->createMock(Observer::class);
        $event = $this->createMock(Event::class);
        $quoteItem = $this->createMock(QuoteItem::class);
        $quote = $this->createMock(Quote::class);
        $quoteItems = [$quoteItem];
        if ($ifMoreThanOneItems) {
            $quoteItems[] = $this->createMock(QuoteItem::class);
        }
        $quote->expects($this->any())->method('getAllVisibleItems')->willReturn($quoteItems);
        $quoteItem->expects($this->any())->method('getQuote')->willReturn($quote);
        $event->expects($this->any())->method('getData')->willReturn($quoteItem);
        $observer->expects($this->any())->method('getEvent')->willReturn($event);

        $actualResult = false;
        $quoteItem->expects($this->any())->method('setHasError')->willReturnCallback(
            function ($isHasError) use (&$actualResult) {
                $actualResult = $isHasError;
            }
        );
        $model->execute($observer);
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function executeDataProvider(): array
    {
        return [
            [
                false,
                true,
                true,
                true,
                false
            ],
            [
                true,
                false,
                true,
                false,
                false
            ],
            [
                false,
                true,
                true,
                false,
                true
            ]
        ];
    }
}
