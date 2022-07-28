<?php

declare(strict_types=1);

namespace Amasty\PreOrderRelease\Plugin\Quote\Model\Quote\Config;

use Amasty\PreOrderRelease\Model\ConfigProvider;
use Magento\Quote\Model\Quote\Config as QuoteConfig;

class AddReleaseDateAttribute
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    public function afterGetProductAttributes(QuoteConfig $subject, array $attributes): array
    {
        $attributes[] = $this->configProvider->getReleaseDateAttribute();
        return $attributes;
    }
}
