<?php declare(strict_types = 1);

namespace Magenest\GoogleTagManager\Test\Unit\Model;

use Magento\Catalog\Model\Category;
use Magenest\GoogleTagManager\Model\Catalog\Category\NameResolver as Model;

use function Magenest\TestingTools\Functions\get;
use function Magenest\TestingTools\Functions\mock;
use function Magenest\TestingTools\Functions\observe;

class NameResolverTest extends \Magenest\TestingTools\Test\Unit\TestCase
{
    public function testResolveShouldNotPassCurrentCategoryToExtractor()
    {
        /** @var Model $model */
        $model = get([
            'extractor..get' => observe($extract),
        ]);

        $category = mock(Category::class, [
            'getPathIds' => [1, 2, 3],
        ]);

        $model->resolve($category);

        $this->assertCalledOnceWith($extract, [[1, 2], 'name', 0]);
    }

    public function testResolveShouldAppendCategoryName()
    {
        /** @var Model $model */
        $model = get([
            'extractor..get' => ['foo', 'bar'],
        ]);

        $category = mock(Category::class, [
            'getName' => 'baz',
            'getPathIds' => [1, 2, 3],
        ]);

        $result = $model->resolve($category);

        $this->assertEquals('foo/bar/baz', $result);
    }

    public function testResolveShouldStripRootCategoryPath()
    {
        /** @var Model $model */
        $model = get([
            'extractor..get' => observe($extract),
            'storeManager..getGroup..getRootCategoryId' => 3,
        ]);

        $category = mock(Category::class, [
            'getPathIds' => [1, 2, 3, 4, 5, 6],
        ]);

        $model->resolve($category);

        $this->assertCalledOnceWith($extract, [[4, 5], 'name', 0]);
    }

    public function testResolveShouldNotReturnRootCategoryName()
    {
        /** @var Model $model */
        $model = get([
            'extractor..get' => observe($extract),
            'storeManager..getGroup..getRootCategoryId' => 2,
        ]);

        $category = mock(Category::class, [
            'getPathIds' => [1, 2],
        ]);

        $path = $model->resolve($category);

        $this->assertNotCalled($extract);
        $this->assertEquals('', $path);
    }

    public function testResolveShouldLimitToFiveCategoryNames()
    {
        /** @var Model $model */
        $model = get([
            'extractor..get' => observe($extract, ['1', '2', '3', '4']),
        ]);

        $category = mock(Category::class, [
            'getPathIds' => [1, 2, 3, 4, 5, 6], // too many levels, '5' will be cut
            'getName' => '6',
        ]);

        $result = $model->resolve($category);

        $this->assertCalledOnceWith($extract, [[1, 2, 3, 4], 'name', 0]);
        $this->assertEquals('1/2/3/4/6', $result);
    }

    public function testResolveShouldReplaceSlashes()
    {
        /** @var Model $model */
        $model = get([
            'extractor..get' => ['1/2', '2'],
            'slashesReplacement' => '-',
        ]);

        $category = mock(Category::class, [
            'getPathIds' => [1, 2],
            'getName' => '3/4',
        ]);

        $result = $model->resolve($category);

        $this->assertEquals('1-2/2/3-4', $result);
    }
}
