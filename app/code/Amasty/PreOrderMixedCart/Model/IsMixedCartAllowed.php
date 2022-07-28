<?php

declare(strict_types=1);

namespace Amasty\PreOrderMixedCart\Model;

class IsMixedCartAllowed
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    public function execute(): bool
    {
        return $this->configProvider->isMixedCartAllowed();
    }
}
