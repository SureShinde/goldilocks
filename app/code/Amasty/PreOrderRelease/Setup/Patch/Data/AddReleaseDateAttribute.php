<?php

declare(strict_types=1);

namespace Amasty\PreOrderRelease\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class AddReleaseDateAttribute implements DataPatchInterface, PatchRevertableInterface
{
    public const ATTRIBUTE_CODE = 'amasty_preorder_release_date';

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
        return [];
    }

    /**
     * @return string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return AddReleaseDateAttribute
     */
    public function apply()
    {
        if ($this->isCanApply()) {
            $this->eavSetup->addAttribute(
                Product::ENTITY,
                self::ATTRIBUTE_CODE,
                [
                    'type' => 'datetime',
                    'backend' => '',
                    'frontend' => '',
                    'input' => 'date',
                    'class' => '',
                    'source' => '',
                    'global' => Attribute::SCOPE_GLOBAL,
                    'visible' => false,
                    'used_in_product_listing' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'unique' => false,
                    'apply_to' => '',
                    'is_configurable' => false
                ]
            );
        }

        return $this;
    }

    public function revert()
    {
        $this->eavSetup->removeAttribute(Product::ENTITY, self::ATTRIBUTE_CODE);
    }

    private function isCanApply(): bool
    {
        return !$this->eavSetup->getAttribute(Product::ENTITY, self::ATTRIBUTE_CODE);
    }
}
