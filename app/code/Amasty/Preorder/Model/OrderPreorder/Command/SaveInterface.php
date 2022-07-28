<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\OrderPreorder\Command;

use Amasty\Preorder\Api\Data\OrderInformationInterface;

interface SaveInterface
{
    public function execute(OrderInformationInterface $orderInformation): void;
}
