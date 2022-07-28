<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Order;

use Amasty\Preorder\Model\OrderPreorder\Query\GetByOrderId;
use Amasty\Preorder\Model\OrderPreorderFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;

class IsPreorder
{
    /**
     * @var GetByOrderId
     */
    private $getByOrderId;

    public function __construct(GetByOrderId $getByOrderId)
    {
        $this->getByOrderId = $getByOrderId;
    }

    public function execute(?Order $order): bool
    {
        if ($order === null) {
            return false;
        }

        try {
            $orderPreorder = $this->getByOrderId->execute((int) $order->getId());
            $result = $orderPreorder->isPreorder();
        } catch (NoSuchEntityException $e) {
            $result = false;
        }

        return $result;
    }
}
