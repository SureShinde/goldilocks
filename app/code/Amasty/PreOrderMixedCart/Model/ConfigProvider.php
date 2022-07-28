<?php

declare(strict_types=1);

namespace Amasty\PreOrderMixedCart\Model;

use Amasty\Preorder\Model\ConfigProvider as PreorderConfigProvider;

class ConfigProvider extends PreorderConfigProvider
{
    public const IS_MIXED_CART_ALLOWED = 'functional/mixed_carts';

    public function isMixedCartAllowed(): bool
    {
        return $this->isSetFlag(self::IS_MIXED_CART_ALLOWED);
    }
}
