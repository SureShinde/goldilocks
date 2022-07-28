<?php
namespace Magenest\GoogleTagManager\Test\Unit\Plugin\Catalog\Block\Product\AbstractProduct;

use Magento\Catalog\Model\Category;
use PHPUnit\Framework\MockObject\MockObject;
use Magenest\GoogleTagManager\Model\Catalog\Category\NameResolver;
use Magenest\GoogleTagManager\Model\Quote\Item\PurchaseCategory;
use Magenest\GoogleTagManager\Plugin\Catalog\Block\Product\AbstractProduct\AddPurchaseCategoryToCartUrl as Model;

use function Magenest\TestingTools\Functions\get;
use function Magenest\TestingTools\Functions\mock;

class AddPurchaseCategoryToCartUrlTest extends \Magenest\TestingTools\Test\Unit\TestCase
{
    public function testBeforeGetAddToCartUrlShouldStopIfDisabled()
    {
        /** @var Model|MockObject $model */
        $model = get([
            'dataHelper..isEnabled' => true,
            'dataHelper..reportQuoteItemCategory' => false,
        ]);

        $subject = null;
        $product = null;
        $additional = [];

        $result = $model->beforeGetAddToCartUrl($subject, $product, $additional);
        self::assertEquals(null, $result); // null implies no change to method parameters
    }

    public function testBeforeGetAddToCartUrlShouldNotResolveCategoryIfDisabled()
    {
        /** @var Model|MockObject $model */
        $model = get([
            'dataHelper..isEnabled' => true,
            'dataHelper..reportQuoteItemCategory' => true,
            'categoryResolver..getCurrentCategory' => mock(Category::class, [
                'getName' => $categoryName = 'simple category name'
            ]),
            /** @var NameResolver|MockObject $nameResolver */
            'nameResolver' => $nameResolver = mock(NameResolver::class),
        ]);

        $subject = null;
        $product = null;
        $additional = [];

        $result = $model->beforeGetAddToCartUrl($subject, $product, $additional);
        $expected = [
            $product,
            [
                '_query' => [
                    PurchaseCategory::PURCHASE_CATEGORY => $categoryName
                ]
            ]
        ];
        self::assertEquals($expected, $result);
    }

    public function testBeforeGetAddToCartUrlShouldResolveCategoryIfEnabled()
    {
        /** @var Model|MockObject $model */
        $model = get([
            'dataHelper..isEnabled' => true,
            'dataHelper..reportQuoteItemCategory' => true,
            'dataHelper..reportParentCategories' => true,
            'categoryResolver..getCurrentCategory' => $category = mock(Category::class, [
                'getName' => $categoryName = 'simple category name'
            ]),
            /** @var NameResolver|MockObject $nameResolver */
            'nameResolver' => $nameResolver = $this->createMock(NameResolver::class)
        ]);

        $subject = null;
        $product = null;
        $additional = [];


        $longCategoryName = 'Foo/Bar/Baz';
        $nameResolver->expects($this->once())
            ->method('resolve')
            ->with($category)
            ->willReturn($longCategoryName);

        $result = $model->beforeGetAddToCartUrl($subject, $product, $additional);
        $expected = [
            $product,
            [
                '_query' => [
                    PurchaseCategory::PURCHASE_CATEGORY => $longCategoryName
                ]
            ]
        ];
        self::assertEquals($expected, $result);
    }
}
