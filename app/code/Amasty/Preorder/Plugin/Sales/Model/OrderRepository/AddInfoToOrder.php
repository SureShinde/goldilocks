<?php

declare(strict_types=1);

namespace Amasty\Preorder\Plugin\Sales\Model\OrderRepository;

use Amasty\Preorder\Model\Order\OrderProcessor;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\OrderRepository;

class AddInfoToOrder
{
    /**
     * @var OrderProcessor
     */
    private $orderProcessor;

    public function __construct(OrderProcessor $orderProcessor)
    {
        $this->orderProcessor = $orderProcessor;
    }

    public function afterGet(OrderRepository $subject, OrderInterface $order): OrderInterface
    {
        $this->orderProcessor->execute([$order]);
        return $order;
    }
}
