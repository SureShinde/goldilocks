<?php

declare(strict_types=1);

namespace Amasty\Preorder\Plugin\Sales\Model\Order\ItemRepository;

use Amasty\Preorder\Model\Order\OrderItemProcessor;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order\ItemRepository;

class AddInfoToItem
{
    /**
     * @var OrderItemProcessor
     */
    private $orderItemProcessor;

    public function __construct(OrderItemProcessor $orderItemProcessor)
    {
        $this->orderItemProcessor = $orderItemProcessor;
    }

    public function afterGet(ItemRepository $subject, OrderItemInterface $orderItem): OrderItemInterface
    {
        $this->orderItemProcessor->execute([$orderItem]);
        return $orderItem;
    }
}
