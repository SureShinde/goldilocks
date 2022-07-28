<?php

declare(strict_types=1);

namespace Amasty\Preorder\ViewModel\Order\View;

use Amasty\Preorder\Api\Data\OrderInformationInterface;
use Amasty\Preorder\Model\Order\GetPreorderInformation;
use Amasty\Preorder\Model\Order\GetWarning;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;

class Preorder implements ArgumentInterface
{
    /**
     * @var GetPreorderInformation
     */
    private $getPreorderInformation;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var GetWarning
     */
    private $getWarning;

    public function __construct(
        GetPreorderInformation $getPreorderInformation,
        Registry $registry,
        GetWarning $getWarning
    ) {
        $this->getPreorderInformation = $getPreorderInformation;
        $this->registry = $registry;
        $this->getWarning = $getWarning;
    }

    public function getCurrentOrder(): OrderInterface
    {
        return $this->registry->registry('current_order');
    }

    public function getPreorderInformation(OrderInterface $order): OrderInformationInterface
    {
        return $this->getPreorderInformation->execute($order);
    }

    public function getWarningHtml(OrderInterface $order): string
    {
        return $this->getWarning->execute((int) $order->getEntityId());
    }

    public function isOrderComplete(OrderInterface $order): bool
    {
        return $order->getState() === Order::STATE_COMPLETE;
    }
}
