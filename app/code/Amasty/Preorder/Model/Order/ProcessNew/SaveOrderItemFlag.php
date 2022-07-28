<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Order\ProcessNew;

use Amasty\Preorder\Api\Data\OrderItemInformationInterface;
use Amasty\Preorder\Model\OrderItemPreorder\Command\SaveInterface;
use Amasty\Preorder\Model\OrderItemPreorder\Query\GetNewInterface;
use Magento\Sales\Api\Data\OrderItemInterface;

class SaveOrderItemFlag implements SaveOrderItemFlagInterface
{
    /**
     * @var GetNewInterface
     */
    private $getNew;

    /**
     * @var SaveInterface
     */
    private $save;

    /**
     * @var GetOrderItemNoteInterface
     */
    private $getOrderItemNote;

    public function __construct(
        GetNewInterface $getNew,
        SaveInterface $save,
        GetOrderItemNoteInterface $getOrderItemNote
    ) {
        $this->getNew = $getNew;
        $this->save = $save;
        $this->getOrderItemNote = $getOrderItemNote;
    }

    public function execute(OrderItemInterface $orderItem): void
    {
        $orderItemFlag = $this->getNew->execute([
            OrderItemInformationInterface::ORDER_ITEM_ID => (int) $orderItem->getItemId(),
            OrderItemInformationInterface::PREORDER_FLAG => true,
            OrderItemInformationInterface::NOTE => $this->getOrderItemNote->execute($orderItem)
        ]);
        $this->save->execute($orderItemFlag);
        $orderItem->getExtensionAttributes()->setPreorderInfo($orderItemFlag);
    }
}
