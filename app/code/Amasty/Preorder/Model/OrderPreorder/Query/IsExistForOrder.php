<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\OrderPreorder\Query;

use Amasty\Preorder\Model\ResourceModel\OrderPreorder\LoadIdByOrderId;

class IsExistForOrder implements IsExistForOrderInterface
{
    /**
     * @var array
     */
    private $cache = [];

    /**
     * @var LoadIdByOrderId
     */
    private $loadIdByOrderId;

    public function __construct(LoadIdByOrderId $loadIdByOrderId)
    {
        $this->loadIdByOrderId = $loadIdByOrderId;
    }

    public function execute(int $orderId): bool
    {
        if (!isset($this->cache[$orderId])) {
            $this->cache[$orderId] = (bool) $this->loadIdByOrderId->execute($orderId);
        }

        return $this->cache[$orderId];
    }

    public function setAsProcessed(int $orderId): void
    {
        $this->cache[$orderId] = true;
    }
}
