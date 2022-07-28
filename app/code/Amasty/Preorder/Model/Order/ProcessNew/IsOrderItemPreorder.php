<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Order\ProcessNew;

use Amasty\Preorder\Model\Product\Detect\IsProductPreorderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;

class IsOrderItemPreorder implements IsOrderItemPreorderInterface
{
    /**
     * @var IsProductPreorderInterface
     */
    private $isProductPreorder;

    public function __construct(IsProductPreorderInterface $isProductPreorder)
    {
        $this->isProductPreorder = $isProductPreorder;
    }

    public function execute(OrderItemInterface $orderItem): bool
    {
        $product = $orderItem->getProduct();

        if ($product === null) {
            return false;
        }

        $result = $this->isProductPreorder->execute($product);

        if (!$result) {
            foreach ($orderItem->getChildrenItems() as $childItem) {
                $result = $this->execute($childItem);
                if ($result) {
                    break;
                }
            }
        }

        return $result;
    }
}
