<?php

namespace Magenest\GoogleTagManager\Test\Unit\Observer;

use Magento\Framework\View\LayoutInterface;

use Magenest\GoogleTagManager\Observer\LayoutLoadBeforeObserver as Model;

use function Magenest\TestingTools\Functions\get;
use function Magenest\TestingTools\Functions\observe;
use function Magenest\TestingTools\Functions\v;
use function Magenest\TestingTools\Functions\mock;
use function Magenest\TestingTools\Functions\r;

class LayoutLoadBeforeObserverTest extends \Magenest\TestingTools\Test\Unit\TestCase
{
    public function testExecuteShouldBootstrapViewWithDataFromObserverWhenGtmEnabled()
    {
        /** @var Model $model */
        $model = get([
            'dataHelper..isEnabled' => true,
            'moduleBootstrap..bootstrapView' => observe($bootstrapView)
        ]);

        $observer = get('observer:', [
            'getData' => r([
                'full_action_name' => v('action'),
                'layout' => $layout = mock(LayoutInterface::class)
            ])
        ]);

        $model->execute($observer);

        $this->assertCalledOnceWith($bootstrapView, [v('action'), $layout]);
    }

    public function testExecuteShouldNotBootstrapViewWhenGtmDisabled()
    {
        /** @var Model $model */
        $model = get([
            'dataHelper..isEnabled' => false,
            'moduleBootstrap..bootstrapView' => $bootstrapView = observe()
        ]);

        $observer = get('observer');

        $model->execute($observer);

        $this->assertNotCalled($bootstrapView);
    }
}
