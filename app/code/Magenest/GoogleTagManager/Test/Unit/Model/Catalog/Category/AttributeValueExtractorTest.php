<?php declare(strict_types = 1);

namespace Magenest\GoogleTagManager\Test\Unit\Model;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magenest\GoogleTagManager\Model\Catalog\Category\AttributeValueExtractor as Model;

use function Magenest\TestingTools\Functions\get;
use function Magenest\TestingTools\Functions\mock;

class AttributeValueExtractorTest extends \Magenest\TestingTools\Test\Unit\TestCase
{
    public function testGetShouldUseCacheWhenAvailable()
    {
        /** @var Model $model */
        $model = get([
            'collectionFactory..create' => $collection = $this->createMock(Collection::class),
        ]);

        $categories = [
            mock(Category::class, [
                'getId' => 1,
                'getData' => '1',
            ]),
            mock(Category::class, [
                'getId' => 2,
                'getData' => '2',
            ]),
        ];

        // invoked when the collection is passed as argument in foreach
        $collection->method('getIterator')->willReturn(new \ArrayIterator($categories));

        $collection->expects($this->any())
            ->method('addIdFilter')
            ->withConsecutive([[1, 2]], [[3, 4]], [[1, 2]], [[1, 2]]);

        $collection->expects($this->exactly(4))->method('getIterator');

        // smoke test
        $model->get([1, 2], 'attribute_code', 0);
        $model->get([1, 2], 'attribute_code', 0);

        // different IDs
        $model->get([3, 4], 'attribute_code', 0);

        // different storeId
        $model->get([1, 2], 'attribute_code', 1);

        // different attribute code
        $model->get([1, 2], 'attribute_code_2', 1);
    }
}
