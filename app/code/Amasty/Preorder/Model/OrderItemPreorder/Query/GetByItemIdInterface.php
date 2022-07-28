<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\OrderItemPreorder\Query;

use Amasty\Preorder\Api\Data\OrderItemInformationInterface;
use Magento\Framework\Exception\NoSuchEntityException;

interface GetByItemIdInterface
{
    /**
     * @param int $orderItemId
     * @return OrderItemInformationInterface
     * @throws NoSuchEntityException
     */
    public function execute(int $orderItemId): OrderItemInformationInterface;
}
