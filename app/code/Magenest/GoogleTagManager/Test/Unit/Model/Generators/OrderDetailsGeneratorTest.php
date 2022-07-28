<?php

namespace Magenest\GoogleTagManager\Test\Unit\Model\Generators;

use Magenest\GoogleTagManager\Model\Generators\OrderDetailsGenerator as Model;

use function Magenest\TestingTools\Functions\get;
use function Magenest\TestingTools\Functions\ra;
use function Magenest\TestingTools\Functions\observe;
use function Magenest\TestingTools\Functions\etc;
use function Magenest\TestingTools\Functions\a;
use function Magenest\TestingTools\Functions\v;
use function Magenest\TestingTools\Functions\mock;

class OrderDetailsGeneratorTest extends \Magenest\TestingTools\Test\Unit\TestCase
{
    public function testGenerateShouldPassCollectorsToCollectorWalker()
    {
        /** @var Model $model */
        $model = get([
            'dataCollectorHelper..walkCollectors' => [
                ra('collectors', ...etc()) => v('result')
            ],
            'dataCollectors' => a('collectors')
        ]);

        $order = get('order');

        $result = $model->generate($order);

        $this->assertSameUid('result', $result);
    }

    public function testGenerateShouldCollectorWalkerWithHandlerThatUsesOrderOnCollector()
    {
        $collector = mock([
            'collect' => observe($collect)
        ]);

        /** @var Model $model */
        $model = get([
            'dataCollectorHelper..walkCollectors' => observe($walkCollectors),
            'dataCollectors' => [$collector]
        ]);

        $order = get('order');

        $model->generate($order);

        $callback = $walkCollectors->arg('callback');

        $callback($collector);

        $this->assertCalledOnceWith($collect, [$order]);
    }
}
