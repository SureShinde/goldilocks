<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\OrderLimit;

use Amasty\DeliveryDateManager\Api\Data\OrderLimitInterface;

class Registry
{
    /**
     * @var OrderLimitInterface[]
     */
    private $storage = [];

    public function get($key): ?OrderLimitInterface
    {
        return $this->storage[$key] ?? null;
    }

    public function set($key, $item): void
    {
        $this->storage[$key] = $item;
    }

    public function unset($key): void
    {
        unset($this->storage[$key]);
    }

    public function isset($key): bool
    {
        return isset($this->storage[$key]);
    }
}
