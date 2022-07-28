<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Order;

use Amasty\Preorder\Model\OrderItemPreorder\Query\GetByItemIdInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class IsItemPreorder
{
    /**
     * @var GetByItemIdInterface
     */
    private $getByItemId;

    public function __construct(GetByItemIdInterface $getByItemId)
    {
        $this->getByItemId = $getByItemId;
    }

    public function execute(int $itemId): bool
    {
        try {
            $orderItemPreorder = $this->getByItemId->execute($itemId);
            $result = $orderItemPreorder->isPreorder();
        } catch (NoSuchEntityException $e) {
            $result = false;
        }

        return $result;
    }
}
