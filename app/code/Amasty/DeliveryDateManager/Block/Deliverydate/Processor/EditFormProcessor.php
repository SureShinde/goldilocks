<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Block\Deliverydate\Processor;

use Amasty\DeliveryDateManager\Block\Component\ComponentInterface;
use Amasty\DeliveryDateManager\Block\Deliverydate\LayoutProcessorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Sales\Api\OrderRepositoryInterface;

class EditFormProcessor implements LayoutProcessorInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var ComponentInterface[]
     */
    private $components;

    public function __construct(
        RequestInterface $request,
        OrderRepositoryInterface $orderRepository,
        ArrayManager $arrayManager,
        array $components = []
    ) {
        $this->request = $request;
        $this->orderRepository = $orderRepository;
        $this->arrayManager = $arrayManager;
        $this->components = $components;
    }

    /**
     * @param array $jsLayout
     * @return array
     */
    public function process(array $jsLayout): array
    {
        $order = $this->orderRepository->get($this->getOrderId());
        $storeId = (int)$order->getStoreId();
        $deliveryDatePath = 'components/amasty-delivery-date/children';

        $deliveryDateChildren = $this->arrayManager->get(
            $deliveryDatePath,
            $jsLayout,
            []
        );

        foreach ($this->components as $component) {
            if (!$component->isEnabled($storeId)) {
                unset($deliveryDateChildren[$component->getName()]);
                continue;
            }

            $deliveryDateChildren[$component->getName()] = $component->getComponent($storeId);
        }

        $jsLayout = $this->arrayManager->set(
            $deliveryDatePath,
            $jsLayout,
            $deliveryDateChildren
        );

        return $jsLayout;
    }

    /**
     * @return int
     */
    private function getOrderId(): int
    {
        return (int)$this->request->getParam('order_id');
    }
}
