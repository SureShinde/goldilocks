<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\OrderPreorder\Query;

use Amasty\Preorder\Api\Data\OrderInformationInterface;

interface GetNewInterface
{
    public function execute(array $data = []): OrderInformationInterface;
}
