<?php declare(strict_types = 1);

namespace Magenest\GoogleTagManager\Test\Unit\Model;

use Magento\Quote\Model\Quote\Item;

use Magenest\GoogleTagManager\Model\CartState as Model;

use function Magenest\TestingTools\Functions\get;
use function Magenest\TestingTools\Functions\r;
use function Magenest\TestingTools\Functions\observe;
use function Magenest\TestingTools\Functions\v;
use function Magenest\TestingTools\Functions\etc;

class CartStateTest extends \Magenest\TestingTools\Test\Unit\TestCase
{
    public function qtyDataProvider()
    {
        return [
            [null, 1, 1],
            ['', 1, 1],
            [1, 2, 1],
            [10, 11, 1],
        ];
    }

    /**
     * @dataProvider qtyDataProvider
     *
     * @param null|string|int $oldQty
     * @param int $newQty
     * @param int $expectedQty
     */
    public function testRegisterQuoteItemShouldSubtractExistingQuantity($oldQty, $newQty, $expectedQty)
    {
        /** @var Model $model */
        $model = get([
            'sessionHelper..addItem' => observe($addItem)
        ]);
        /** @var Item $item */
        $item = get('item', [
            'getOrigData' => r([Item::KEY_QTY => $oldQty]),
            'getQty' => $newQty
        ]);

        $model->registerQuoteItem($item);

        // no support in Magenest tt for \PHPUnit\Framework\Constraint\Constraint :(
        $this->assertCalledOnceWith($addItem, [$item, $expectedQty]);
    }
}
