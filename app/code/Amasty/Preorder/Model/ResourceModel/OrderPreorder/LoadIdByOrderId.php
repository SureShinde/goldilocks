<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\ResourceModel\OrderPreorder;

use Amasty\Preorder\Api\Data\OrderInformationInterface;
use Amasty\Preorder\Model\ResourceModel\OrderPreorder as OrderPreorderResource;

class LoadIdByOrderId
{
    /**
     * @var OrderPreorderResource
     */
    private $orderPreorderResource;

    public function __construct(OrderPreorderResource $orderPreorderResource)
    {
        $this->orderPreorderResource = $orderPreorderResource;
    }

    public function execute(int $orderId): int
    {
        $select = $this->orderPreorderResource->getConnection()->select()->from(
            $this->orderPreorderResource->getMainTable(),
            [OrderInformationInterface::ID]
        )->where(sprintf('%s = ?', OrderInformationInterface::ORDER_ID), $orderId);

        return (int) $this->orderPreorderResource->getConnection()->fetchOne($select);
    }
}
