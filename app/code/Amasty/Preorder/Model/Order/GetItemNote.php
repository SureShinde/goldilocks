<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Order;

use Amasty\Preorder\Model\OrderItemPreorder;
use Amasty\Preorder\Model\OrderItemPreorder\Query\GetByItemIdInterface;
use Amasty\Preorder\Model\Product\RetrieveNote\GetNote as GetProductNote;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order\Item as OrderItem;

class GetItemNote
{
    /**
     * @var GetProductNote
     */
    private $getProductNote;

    /**
     * @var GetByItemIdInterface
     */
    private $getByItemId;

    public function __construct(
        GetProductNote $getProductNote,
        GetByItemIdInterface $getByItemId
    ) {
        $this->getProductNote = $getProductNote;
        $this->getByItemId = $getByItemId;
    }

    public function execute(OrderItem $orderItem): string
    {
        try {
            /** @var OrderItemPreorder $orderItemPreorder */
            $orderItemPreorder = $this->getByItemId->execute((int)$orderItem->getItemId());
            $note = $orderItemPreorder->getNote();
        } catch (NoSuchEntityException $e) {
            $note = '';
        }

        return $note;
    }
}
