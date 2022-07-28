<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Order\ProcessNew;

use Amasty\Preorder\Model\Product\GetPreorderInformation;
use Magento\Sales\Api\Data\OrderItemInterface;

class GetOrderItemNote implements GetOrderItemNoteInterface
{
    /**
     * @var GetPreorderInformation
     */
    private $getPreorderInformation;

    public function __construct(GetPreorderInformation $getPreorderInformation)
    {
        $this->getPreorderInformation = $getPreorderInformation;
    }

    public function execute(OrderItemInterface $orderItem): string
    {
        $product = $orderItem->getProduct();

        if ($orderItem->getProductType() == 'configurable') {
            $children = $orderItem->getChildrenItems();
            if (isset($children[0])) {
                $product = $children[0]->getProduct();
            }
        }

        if ($product) {
            $note = $this->getPreorderInformation->execute($product)->getNote();
        } else {
            $note = '';
        }

        return $note;
    }
}
