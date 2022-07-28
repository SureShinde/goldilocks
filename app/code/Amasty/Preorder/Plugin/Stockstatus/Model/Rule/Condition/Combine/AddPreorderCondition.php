<?php

declare(strict_types=1);

namespace Amasty\Preorder\Plugin\Stockstatus\Model\Rule\Condition\Combine;

use Amasty\Preorder\Model\Condition\AddPreorderCondition as AddPreorderConditionToArray;
use Amasty\Preorder\Model\Condition\CatalogRule\Preorder;
use Amasty\Stockstatus\Model\Rule\Condition\Combine;

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
