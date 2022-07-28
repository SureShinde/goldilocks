<?php

namespace Magenest\AdvancedLogin\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\Attribute;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Customer\Setup\CustomerSetupFactory;


class CreateIsOtpConfirmAttributeCustomer implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /** @var CustomerSetupFactory  */
    protected $customerSetupFactory;

    /** @var AttributeSetFactory  */
    protected $attributeSetFactory;

    /** @var Attribute  */
    protected $_customerAttribute;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     * @param Attribute $customerAttribute
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory,
        Attribute $customerAttribute
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory  = $attributeSetFactory;
        $this->_customerAttribute = $customerAttribute;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->createPhoneAttribute();
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    public function createPhoneAttribute()
    {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
        $customerSetup->addAttribute(
            Customer::ENTITY,
            'is_otp_confirm',
            [
                'type' => 'int',
                'label' => 'Is Otp Confirm',
                'input' => 'select',
                'source' => Boolean::class,
                'visible'  => true,
                'required' => false,
                'default'  => 0,
                'frontend' => '',
                'note'     => '',
                'class' => '',
                'searchable' => false,
                'filterable' => false,
                'system' => 0,
                'position' => 100,
                'user_defined' => false,
                'sort_order' => 1000,
                'unique' => false,
                'is_used_in_grid' => 1,
                'is_visible_in_grid' => 1,
                'is_filterable_in_grid' => 1,
                'is_searchable_in_grid' => 1,
            ]
        );

        $phoneAttribute    = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'is_otp_confirm');
        $usedInForms = ['adminhtml_customer'];
        $phoneAttribute->addData([
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'is_used_for_customer_segment' => true,
            'is_system' => 0,
            'is_user_defined' => 1,
            'is_visible' => 1,
            'sort_order' => 100,
            'used_in_forms' => $usedInForms
        ]);
        $this->_customerAttribute->save($phoneAttribute);
    }
}

