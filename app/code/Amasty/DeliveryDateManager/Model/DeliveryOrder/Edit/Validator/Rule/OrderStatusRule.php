<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryOrder\Edit\Validator\Rule;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateOrderInterface;
use Amasty\DeliveryDateManager\Model\EditableConfigProvider;
use Magento\Framework\App\ScopeInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderStatusRule implements RuleInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var EditableConfigProvider
     */
    private $configProvider;

    public function __construct(OrderRepositoryInterface $orderRepository, EditableConfigProvider $configProvider)
    {
        $this->orderRepository = $orderRepository;
        $this->configProvider = $configProvider;
    }

    /**
     * @param DeliveryDateOrderInterface $deliveryDateOrder
     * @param int|ScopeInterface|null $store
     * @return bool
     */
    public function validate(DeliveryDateOrderInterface $deliveryDateOrder, $store = null): bool
    {
        $order = $this->orderRepository->get($deliveryDateOrder->getOrderId());
        $status = (string)$order->getStatus();
        $rescheduleStatus = $this->configProvider->getOrderStatuses($store);

        return in_array($status, $rescheduleStatus, true);
    }
}
