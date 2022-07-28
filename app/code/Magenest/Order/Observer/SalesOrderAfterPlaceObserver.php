<?php

namespace Magenest\Order\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

class SalesOrderAfterPlaceObserver implements ObserverInterface
{
    private RequestInterface $request;

    /**
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**]
     * @param EventObserver $observer
     * @return $this|void
     * @throws \Exception
     */
    public function execute(EventObserver $observer)
    {
        /** @var Order $order */
        $order = $observer->getOrder();
        $orderParams = $this->request->getParam('order');
        if ($orderParams && isset($orderParams['source']) && $orderParams['source']) {
            $sourceData = json_encode($orderParams['source']);
            $order->setData('source_field', $sourceData);
            $order->save();
        }
        return $this;
    }
}
