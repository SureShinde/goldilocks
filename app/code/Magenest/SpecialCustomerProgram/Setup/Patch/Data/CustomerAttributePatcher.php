<?php
namespace Magenest\SpecialCustomerProgram\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Config;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CustomerAttributePatcher implements DataPatchInterface
{
    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * AccountPurposeCustomerAttribute constructor.
     * @param ModuleDataSetupInterface $setup
     * @param Config $eavConfig
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        Config $eavConfig,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->setup = $setup;
        $this->eavConfig = $eavConfig;
    }

    /** We'll add our customer attribute here */
    public function apply()
    {
        $ciAttributes = ['CI Number','CI Full Name','CI Image'];
        foreach ($ciAttributes as $attribute) {
            $attributeCode = str_replace(' ', '_', strtolower($attribute));
            $customerSetup = $this->customerSetupFactory->create(['setup' => $this->setup]);
            $customerSetup->addAttribute(Customer::ENTITY, $attributeCode, [
                'type' => 'text',
                'visible' => false,
                'required' => false,
                'user_defined' => false,
                'label' => $attribute,
                'system' => false
            ]);
        }
        $newAttribute = $this->eavConfig->getAttribute(Customer::ENTITY, $attributeCode);
        $newAttribute->save();
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
