<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\OrderItemPreorder\Command;

use Amasty\Preorder\Api\Data\OrderItemInformationInterface;

interface SaveInterface
{
    public function execute(OrderItemInformationInterface $orderItemInformation): void;
}
