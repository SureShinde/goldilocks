<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\ViewModel\DeliveryDate;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateOrderInterface;
use Amasty\DeliveryDateManager\Model\CalendarRepository;
use Amasty\DeliveryDateManager\Model\DeliveryChannelScope\ScopeRegistry;
use Amasty\DeliveryDateManager\Model\DeliveryChannelScope\ShippingMethodScopeData;
use Amasty\DeliveryDateManager\Model\DeliveryChannelScope\StoreViewScopeData;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\Get;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\OutputFormatter;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class Edit implements ArgumentInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var Get
     */
    private $deliveryOrderGet;

    /**
     * @var CalendarRepository
     */
    private $calendarRepository;

    /**
     * @var ScopeRegistry
     */
    private $scopeRegistry;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $json;

    /**
     * @var OutputFormatter
     */
    private $outputFormatter;

    /**
     * @var \Amasty\DeliveryDateManager\Model\CheckoutConfigProvider
     */
    private $checkoutConfigProvider;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        Get $deliveryOrderGet,
        CalendarRepository $calendarRepository,
        ScopeRegistry $scopeRegistry,
        \Magento\Framework\Serialize\Serializer\Json $json,
        OutputFormatter $outputFormatter,
        \Amasty\DeliveryDateManager\Model\CheckoutConfigProvider $checkoutConfigProvider
    ) {
        $this->orderRepository = $orderRepository;
        $this->deliveryOrderGet = $deliveryOrderGet;
        $this->calendarRepository = $calendarRepository;
        $this->scopeRegistry = $scopeRegistry;
        $this->json = $json;
        $this->outputFormatter = $outputFormatter;
        $this->checkoutConfigProvider = $checkoutConfigProvider;
    }

    /**
     * @param int $orderId
     * @return OrderInterface
     */
    public function getOrder(int $orderId): OrderInterface
    {
        return $this->orderRepository->get($orderId);
    }

    /**
     * @param int $orderId
     * @return DeliveryDateOrderInterface
     */
    public function getDeliveryDate(int $orderId): DeliveryDateOrderInterface
    {
        return $this->deliveryOrderGet->getByOrderId($orderId);
    }

    /**
     * @param int $orderId
     * @return string
     */
    public function getChannelSetJson(int $orderId): string
    {
        $order = $this->getOrder($orderId);
        $this->scopeRegistry->reset();
        $this->scopeRegistry->collectScopesFromOrder($order);

        $channelSet = $this->calendarRepository->getCalendarSet();

        return $this->json->serialize($channelSet);
    }

    /**
     * @param DeliveryDateOrderInterface $deliveryDateOrder
     * @return string
     */
    public function formatDate(DeliveryDateOrderInterface $deliveryDateOrder): string
    {
        return $this->outputFormatter->getFormattedDateFromDeliveryOrder($deliveryDateOrder);
    }

    /**
     * @param DeliveryDateOrderInterface $deliveryDateOrder
     * @return string
     */
    public function formatTimeInterval(DeliveryDateOrderInterface $deliveryDateOrder): string
    {
        return $this->outputFormatter->getTimeLabelFromDeliveryOrder($deliveryDateOrder);
    }

    /**
     * @param DeliveryDateOrderInterface $deliveryDateOrder
     * @return string
     */
    public function formatDeliveryComments(DeliveryDateOrderInterface $deliveryDateOrder): string
    {
        return $this->outputFormatter->getComment($deliveryDateOrder);
    }

    /**
     * @return string
     */
    public function getConfigJson(): string
    {
        return $this->json->serialize($this->checkoutConfigProvider->getConfig());
    }
}
