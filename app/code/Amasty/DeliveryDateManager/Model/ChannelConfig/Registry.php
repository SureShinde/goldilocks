<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ChannelConfig;

use Amasty\DeliveryDateManager\Api\Data\ChannelConfigDataInterface;

class Registry
{
    /**
     * @var ChannelConfigDataInterface[]
     */
    private $storage = [];

    public function get($key): ?ChannelConfigDataInterface
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
