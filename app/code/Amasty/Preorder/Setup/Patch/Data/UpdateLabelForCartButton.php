<?php

declare(strict_types=1);

namespace Amasty\Preorder\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateLabelForCartButton implements DataPatchInterface
{
    /**
     * @var EavSetup
     */
    private $eavSetup;

    public function __construct(EavSetupFactory $eavSetupFactory, ModuleDataSetupInterface $setup)
    {
        $this->eavSetup = $eavSetupFactory->create(['setup' => $setup]);
    }

    /**
     * @return string[]
     */
    public static function getDependencies()
    {
        return [AddCartLabelAttribute::class];
    }

    /**
     * @return UpdateLabelForCartButton
     */
    public function apply()
    {
        $this->eavSetup->updateAttribute(
            Product::ENTITY,
            AddCartLabelAttribute::ATTRIBUTE_NAME,
            'frontend_label',
            __('Pre-Order Cart Button')
        );

        return $this;
    }

    /**
     * @return string[]
     */
    public function getAliases()
    {
        return [];
    }
}
