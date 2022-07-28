<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Product;

use Magento\Framework\Module\Manager as ModuleManager;

class IsInventoryEnabled
{
    /**
     * @var ModuleManager
     */
    private $moduleManager;

    public function __construct(ModuleManager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    public function execute(): bool
    {
        return $this->moduleManager->isEnabled('Magento_Inventory');
    }
}
