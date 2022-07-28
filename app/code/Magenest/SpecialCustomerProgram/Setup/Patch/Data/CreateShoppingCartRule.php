<?php
declare(strict_types=1);

namespace Magenest\SpecialCustomerProgram\Setup\Patch\Data;

use Magento\Customer\Model\ResourceModel\Group\CollectionFactory as GroupCollectionFactory;
use Magento\Framework\App\State;
use Magento\Framework\Module\ResourceInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\SalesRule\Api\Data\RuleInterfaceFactory;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Model\Data\Rule;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory as WebsiteCollectionFactory;
use Magento\SalesRule\Api\Data\RuleInterface;

class CreateShoppingCartRule implements DataPatchInterface
{
    /**
     * @var State
     */
    private $appState;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var GroupCollectionFactory
     */
    private $customerGroupCollectionFactory;

    /**
     * @var WebsiteCollectionFactory
     */
    private $websiteCollectionFactory;

    /**
     * @var RuleInterfaceFactory
     */
    private $ruleFactory;

    /**
     * @var ResourceInterface
     */
    private $moduleResource;

    /**
     * @param State $appState
     * @param RuleInterfaceFactory $ruleFactory
     * @param WebsiteCollectionFactory $websiteCollectionFactory
     * @param GroupCollectionFactory $customerGroupCollectionFactory
     * @param RuleRepositoryInterface $ruleRepository
     * @param ResourceInterface $moduleResource
     */
    public function __construct(
        State $appState,
        RuleInterfaceFactory $ruleFactory,
        WebsiteCollectionFactory $websiteCollectionFactory,
        GroupCollectionFactory $customerGroupCollectionFactory,
        RuleRepositoryInterface $ruleRepository,
        ResourceInterface $moduleResource
    ) {
        $this->appState = $appState;
        $this->ruleFactory = $ruleFactory;
        $this->websiteCollectionFactory = $websiteCollectionFactory;
        $this->customerGroupCollectionFactory = $customerGroupCollectionFactory;
        $this->ruleRepository = $ruleRepository;
        $this->moduleResource = $moduleResource;
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function apply(): void
    {
        $this->appState->emulateAreaCode('adminhtml', [$this, 'createShoppingCartRule']);
    }

    /**
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createShoppingCartRule(): void
    {
        $data = [
            'name' => 'Special Customer Program',
            'is_active' => 1,
            'simple_action' => 'by_percent',
            'discount_amount' => '20.0000',
            'coupon_type' => 1,
            'special_customer_program' => 1
        ];
        /** @var Rule $rule */
        $rule = $this->ruleFactory->create(['data' => $data]);
        $websites = $this->websiteCollectionFactory->create()->toOptionHash();
        $websiteIds = array_keys($websites);
        $rule->setWebsiteIds($websiteIds);
        $customerGroups = $this->customerGroupCollectionFactory->create()->toOptionHash();
        $customerGroupIds = array_keys($customerGroups);
        $rule->setCustomerGroupIds($customerGroupIds);
        $rule->setStopRulesProcessing(false);
        $this->ruleRepository->save($rule);
    }
}
