<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Order\ProcessNew;

use Magento\Sales\Api\Data\OrderItemInterface;

interface IsOrderItemPreorderInterface
{
    public function execute(OrderItemInterface $orderItem): bool;
}
