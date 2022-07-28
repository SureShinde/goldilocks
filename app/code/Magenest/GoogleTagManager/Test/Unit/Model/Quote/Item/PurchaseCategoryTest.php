<?php declare(strict_types = 1);

namespace Magenest\GoogleTagManager\Test\Unit\Model\Quote\Item;

use Magenest\GoogleTagManager\Model\Quote\Item\PurchaseCategory as Model;

use function Magenest\TestingTools\Functions\get;
use function Magenest\TestingTools\Functions\observe;

class PurchaseCategoryTest extends \Magenest\TestingTools\Test\Unit\TestCase
{
    public function testGetShouldExtractFromBuyRequest()
    {
        /** @var Model $model */
        $model = get();

        $item = get('item', [
            'getBuyRequest..getData' => observe($getData, 'Foo/Bar'),
        ]);

        $category = $model->get($item);

        $this->assertCalledOnceWith($getData, [Model::PURCHASE_CATEGORY]);
        $this->assertEquals('Foo/Bar', $category);
    }
}
