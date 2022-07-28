<?php

declare(strict_types=1);

namespace Amasty\Preorder\Plugin\SalesRule\Model\Rule\Condition\Product\Combine;

use Amasty\Preorder\Model\Condition\AddPreorderCondition as AddPreorderConditionToArray;
use Amasty\Preorder\Model\Condition\SalesRule\Preorder;
use Magento\SalesRule\Model\Rule\Condition\Product\Combine;

class AddPreorderCondition
{
    /**
     * @var AddPreorderConditionToArray
     */
    private $addPreorderCondition;

    public function __construct(AddPreorderConditionToArray $addPreorderCondition)
    {
        $this->addPreorderCondition = $addPreorderCondition;
    }

    public function afterGetNewChildSelectOptions(Combine $subject, array $conditions): array
    {
        return $this->addPreorderCondition->execute($conditions, Preorder::class);
    }
}
