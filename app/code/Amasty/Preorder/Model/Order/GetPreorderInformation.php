<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Order;

use Amasty\Preorder\Api\Data\OrderInformationInterface;
use Magento\Sales\Api\Data\OrderInterface;

class GetPreorderInformation
{
    /**
     * @var OrderProcessor
     */
    private $orderProcessor;

    public function __construct(OrderProcessor $orderProcessor)
    {
        $this->orderProcessor = $orderProcessor;
    }

    public function execute(OrderInterface $order): OrderInformationInterface
    {
        if ($order->getExtensionAttributes()->getPreorderInfo() === null) {
            $this->orderProcessor->execute([$order]);
        }

        return $order->getExtensionAttributes()->getPreorderInfo();
    }
}
