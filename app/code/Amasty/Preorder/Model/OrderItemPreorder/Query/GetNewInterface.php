<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\OrderItemPreorder\Query;

use Amasty\Preorder\Api\Data\OrderItemInformationInterface;

interface GetNewInterface
{
    public function execute(array $data = []): OrderItemInformationInterface;
}
