<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryOrder\Edit\Validator;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateOrderInterface;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\Edit\Validator\Rule\RuleInterface;
use Amasty\DeliveryDateManager\Model\EditableConfigProvider;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\ObjectManagerInterface;

class EditableOnFront
{
    /**
     * @var EditableConfigProvider
     */
    private $configProvider;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array
     */
    private $rules;

    public function __construct(
        EditableConfigProvider $configProvider,
        ObjectManagerInterface $objectManager,
        array $rules = []
    ) {
        $this->configProvider = $configProvider;
        $this->objectManager = $objectManager;
        $this->rules = $rules;
    }

    /**
     * @param DeliveryDateOrderInterface $deliveryDateOrder
     * @param int|ScopeInterface|null $store
     * @return bool
     */
    public function validate(DeliveryDateOrderInterface $deliveryDateOrder, $store = null): bool
    {
        if (!$this->configProvider->isEditable($store)) {
            return false;
        }
        $activationRule = $this->configProvider->getRuleActivation($store);

        $ruleClass = $this->rules[$activationRule] ?? null;
        if (!$ruleClass) {
            return false;
        }

        /** @var RuleInterface $rule */
        $rule = $this->objectManager->get($ruleClass);

        return $rule->validate($deliveryDateOrder, $store);
    }
}
