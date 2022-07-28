<?php

declare(strict_types=1);

namespace Amasty\Preorder\Plugin\Sales\Model\Service\OrderService;

use Amasty\Preorder\Model\ConfigProvider;
use Amasty\Preorder\Model\Order\ProcessNew;
use Amasty\Preorder\Model\OrderPreorder\Query\IsExistForOrderInterface;
use Exception;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Service\OrderService;
use Psr\Log\LoggerInterface;

class ProcessNewOrder
{
    /**
     * @var ProcessNew
     */
    private $processNewOrder;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var IsExistForOrderInterface
     */
    private $isExistForOrder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ProcessNew $processNewOrder,
        ConfigProvider $configProvider,
        IsExistForOrderInterface $isExistForOrder,
        LoggerInterface $logger
    ) {
        $this->processNewOrder = $processNewOrder;
        $this->configProvider = $configProvider;
        $this->isExistForOrder = $isExistForOrder;
        $this->logger = $logger;
    }

    public function afterPlace(OrderService $subject, OrderInterface $order): OrderInterface
    {
        if ($this->configProvider->isPreorderEnabled()) {
            try {
                $this->checkNewOrder($order);
            } catch (Exception $e) {
                $this->logger->critical($e);
            }
        }

        return $order;
    }

    private function checkNewOrder(OrderInterface $order): void
    {
        $alreadyProcessed = $order->getId() && $this->isExistForOrder->execute((int) $order->getId());
        if (!$alreadyProcessed) {
            $this->processNewOrder->execute($order);
            $this->isExistForOrder->setAsProcessed((int) $order->getId());
        }
    }
}
