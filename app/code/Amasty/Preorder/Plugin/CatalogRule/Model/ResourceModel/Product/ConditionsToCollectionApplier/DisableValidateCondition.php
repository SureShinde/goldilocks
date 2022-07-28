<?php

declare(strict_types=1);

namespace Amasty\Preorder\Plugin\CatalogRule\Model\ResourceModel\Product\ConditionsToCollectionApplier;

use Amasty\Preorder\Model\Condition\CatalogRule\Preorder;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\CatalogRule\Model\ResourceModel\Product\ConditionsToCollectionApplier;
use Magento\CatalogRule\Model\Rule\Condition\Combine;
use Magento\CatalogRule\Model\Rule\Condition\MappableConditionsProcessor;

/**
 * Class DisableValidateCondition
 *
 * Remove our custom preorder condition because Magento disable custom conditions.
 * @see Preorder::class
 * @see MappableConditionsProcessor::rebuildCombinedCondition
 */
class DisableValidateCondition
{
    public function beforeApplyConditionsToCollection(
        ConditionsToCollectionApplier $subject,
        Combine $conditions,
        ProductCollection $productCollection
    ): array {
        $newConditions = clone $conditions;
        $this->removeOurCondition($newConditions);
        return [$newConditions, $productCollection];
    }

    private function removeOurCondition(Combine $conditions): void
    {
        $conditionsArray = [];
        foreach ($conditions->getConditions() as $condition) {
            if (!$condition instanceof Preorder) {
                if ($condition instanceof Combine) {
                    $newCondition = clone $condition;
                    $this->removeOurCondition($newCondition);
                    $condition = $newCondition;
                }
                $conditionsArray[] = $condition;
            }
        }
        $conditions->setConditions($conditionsArray);
    }
}
