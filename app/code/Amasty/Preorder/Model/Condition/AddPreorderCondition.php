<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Condition;

class AddPreorderCondition
{
    public function execute(array $conditions, string $conditionClass): array
    {
        $label = __('Product Attribute');
        foreach ($conditions as &$condition) {
            if ((string) $condition['label'] === (string) $label) {
                $condition['value'][] = [
                    'label' => __('Pre-order (Amasty Pre Order)'),
                    'value' => $conditionClass
                ];
            }
        }

        return $conditions;
    }
}
