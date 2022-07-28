<?php

namespace Magenest\GoogleTagManager\Test\Unit\Observer;

use Magenest\GoogleTagManager\Model\CartState;

use Magenest\GoogleTagManager\Observer\RegisterProductAddedToCart as Model;

use function Magenest\TestingTools\Functions\get;
use function Magenest\TestingTools\Functions\mock;
use function Magenest\TestingTools\Functions\r;

class RegisterProductAddedToCartTest extends \Magenest\TestingTools\Test\Unit\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CartState
     */
    private $cartState;

    public function testExecuteShouldPassQuoteToRegisterFunction()
    {
        /** @var Model $model */
        $model = get([
            'dataHelper..isEnabled' => true,
            'cartState' => $this->cartState = $this->createMock(CartState::class)
        ]);

        $observer = get('observer:', [
            'getData' => r([
                'quote_item' => $quoteItem = mock(\Magento\Quote\Model\Quote\Item::class)
            ])
        ]);

        $this->cartState->expects($this->once())->method('registerQuoteItem')->with($quoteItem);

        $model->execute($observer);
    }
}
