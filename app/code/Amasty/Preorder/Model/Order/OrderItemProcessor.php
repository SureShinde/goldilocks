<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Order;

use Amasty\Preorder\Model\OrderItemPreorder\Query\GetByItemIdInterface;
use Amasty\Preorder\Model\OrderItemPreorder\Query\GetNewInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderItemInterface;

class OrderItemProcessor
{
    /**
     * @var GetByItemIdInterface
     */
    private $getByItemId;

    /**
     * @var GetNewInterface
     */
    private $getNew;

    public function __construct(
        GetByItemIdInterface $getByItemId,
        GetNewInterface $getNew
    ) {
        $this->getByItemId = $getByItemId;
        $this->getNew = $getNew;
    }

    /**
     * @param OrderItemInterface[] $orderItems
     * @return void
     */
    public function execute(array $orderItems): void
    {
        foreach ($orderItems as $orderItem) {
            if (!$orderItem->getExtensionAttributes()->getPreorderInfo()) {
                try {
                    $preorderInformation = $this->getByItemId->execute((int) $orderItem->getItemId());
                } catch (NoSuchEntityException $e) {
                    $preorderInformation = $this->getNew->execute();
                }

                $orderItem->getExtensionAttributes()->setPreorderInfo($preorderInformation);
            }
        }
    }
}
