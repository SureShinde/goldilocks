<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Order;

use Amasty\Preorder\Model\OrderPreorder\Query\GetByOrderIdInterface;
use Amasty\Preorder\Model\OrderPreorder\Query\GetNewInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;

class OrderProcessor
{
    /**
     * @var GetByOrderIdInterface
     */
    private $getByOrderId;

    /**
     * @var GetNewInterface
     */
    private $getNew;

    public function __construct(
        GetByOrderIdInterface $getByOrderId,
        GetNewInterface $getNew
    ) {
        $this->getByOrderId = $getByOrderId;
        $this->getNew = $getNew;
    }

    /**
     * @param OrderInterface[] $orders
     * @return void
     */
    public function execute(array $orders): void
    {
        foreach ($orders as $order) {
            if (!$order->getExtensionAttributes()->getPreorderInfo()) {
                try {
                    $preorderInformation = $this->getByOrderId->execute((int) $order->getEntityId());
                } catch (NoSuchEntityException $e) {
                    $preorderInformation = $this->getNew->execute();
                }

                $order->getExtensionAttributes()->setPreorderInfo($preorderInformation);
            }
        }
    }
}
