<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Order\ProcessNew;

use Magento\Sales\Api\Data\OrderInterface;

interface SaveOrderFlagInterface
{
    public function execute(OrderInterface $order): void;
}
