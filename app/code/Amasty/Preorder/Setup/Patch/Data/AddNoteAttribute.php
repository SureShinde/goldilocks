<?php

declare(strict_types=1);

namespace Amasty\Preorder\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Zend_Validate_Exception;

class AddNoteAttribute implements DataPatchInterface, PatchRevertableInterface
{
    public const ATTRIBUTE_NAME = 'amasty_preorder_note';

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
     * @return AddNoteAttribute
     * @throws LocalizedException
     * @throws Zend_Validate_Exception
     */
    public function apply()
    {
        if ($this->isCanApply()) {
            $this->eavSetup->addAttribute(
                Product::ENTITY,
                self::ATTRIBUTE_NAME,
                [
                    'type' => 'varchar',
                    'backend'  => '',
                    'frontend' => '',
                    'label' => __('Pre-Order Note'),
                    'input' => 'hidden',
                    'class' => '',
                    'source' => '',
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => false,
                    'required' => false,
                    'user_defined' => false,
                    'default'  => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'unique'  => false,
                    'apply_to' => '',
                    'is_configurable' => false,
                    'used_in_product_listing' => true
                ]
            );
        }

        return $this;
    }

    /**
     * @return string[]
     */
    public function getAliases()
    {
        return [];
    }

    public function revert()
    {
        $this->eavSetup->removeAttribute(Product::ENTITY, self::ATTRIBUTE_NAME);
    }

    private function isCanApply(): bool
    {
        return !$this->eavSetup->getAttribute(Product::ENTITY, self::ATTRIBUTE_NAME);
    }
}
