<?php

declare(strict_types=1);

namespace Amasty\Preorder\Plugin\Quote\Model\Quote\Item\Repository;

use Amasty\Preorder\Model\Quote\Item\Processor;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Item\Repository;

class AddInfoToItems
{
    /**
     * @var Processor
     */
    private $processor;

    public function __construct(Processor $processor)
    {
        $this->processor = $processor;
    }

    /**
     * @param Repository $subject
     * @param CartItemInterface[] $items
     * @return CartItemInterface[]
     */
    public function afterGetList(Repository $subject, array $items): array
    {
        $this->processor->execute($items);
        return $items;
    }
}
