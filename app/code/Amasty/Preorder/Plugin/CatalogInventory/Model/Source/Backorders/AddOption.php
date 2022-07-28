<?php

declare(strict_types=1);

namespace Amasty\Preorder\Plugin\CatalogInventory\Model\Source\Backorders;

use Amasty\Preorder\Model\Product\Constants;
use Magento\CatalogInventory\Model\Source\Backorders;

class AddOption
{
    public function afterToOptionArray(Backorders $subject, array $optionArray): array
    {
        $optionArray[] = [
            'value' => Constants::BACKORDERS_PREORDER_OPTION,
            'label'=> __('Allow Pre-Orders')
        ];

        return $optionArray;
    }
}
