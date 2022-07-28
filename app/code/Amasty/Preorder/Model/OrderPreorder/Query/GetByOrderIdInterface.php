<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\OrderPreorder\Query;

use Amasty\Preorder\Api\Data\OrderInformationInterface;
use Magento\Framework\Exception\NoSuchEntityException;

interface GetByOrderIdInterface
{
    /**
     * @param int $orderId
     * @return OrderInformationInterface
     * @throws NoSuchEntityException
     */
    public function execute(int $orderId): OrderInformationInterface;
}
