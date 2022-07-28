<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryOrder\Edit\Validator\Rule;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateOrderInterface;
use Magento\Framework\App\ScopeInterface;

class CombineRule implements RuleInterface
{
    public const ALL = 'all';
    public const ONE = 'one';

    /**
     * @var RuleInterface[]
     */
    private $rules;

    /**
     * @var string
     */
    private $mode;

    public function __construct(array $rules, string $mode)
    {
        $this->rules = $rules;
        $this->mode = $mode;
    }

    /**
     * @param DeliveryDateOrderInterface $deliveryDateOrder
     * @param int|ScopeInterface|null $store
     * @return bool
     */
    public function validate(DeliveryDateOrderInterface $deliveryDateOrder, $store = null): bool
    {
        $isModeAll = $this->mode === self::ALL;

        foreach ($this->rules as $rule) {
            $ruleValidationResult = $rule->validate($deliveryDateOrder, $store);

            if ($ruleValidationResult && !$isModeAll) {
                return true;
            }

            if (!$ruleValidationResult && $isModeAll) {
                return false;
            }
        }

        return $isModeAll;
    }
}
