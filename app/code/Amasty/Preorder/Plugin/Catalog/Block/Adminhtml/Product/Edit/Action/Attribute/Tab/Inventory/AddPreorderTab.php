<?php

declare(strict_types=1);

namespace Amasty\Preorder\Plugin\Catalog\Block\Adminhtml\Product\Edit\Action\Attribute\Tab\Inventory;

use Magento\Catalog\Block\Adminhtml\Product\Edit\Action\Attribute\Tab\Inventory;

class AddPreorderTab
{
    public const PREORDER_BLOCK_NAME = 'mass.product.preorder';

    public function afterToHtml(Inventory $subject, string $html): string
    {
        $preOrderHtml = $subject->getLayout()
            ->getBlock(self::PREORDER_BLOCK_NAME)
            ->toHtml();

        return $html . $preOrderHtml;
    }
}
