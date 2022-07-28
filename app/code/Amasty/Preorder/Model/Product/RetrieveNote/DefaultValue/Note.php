<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Product\RetrieveNote\DefaultValue;

use Amasty\Preorder\Model\ConfigProvider;

class Note implements RetrieverInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    public function execute(): string
    {
        return $this->configProvider->getDefaultPreorderNote();
    }
}
