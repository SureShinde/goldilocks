<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\OrderPreorder\Query;

interface IsExistForOrderInterface
{
    /**
     * @param int $orderId
     * @return bool
     */
    public function execute(int $orderId): bool;

    /**
     * @param int $orderId
     * @return void
     */
    public function setAsProcessed(int $orderId): void;
}
