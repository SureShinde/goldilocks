<?php

declare(strict_types=1);

namespace Amasty\Preorder\Plugin\Sales\Model\Order\Item;

use Amasty\Preorder\Model\ConfigProvider;
use Amasty\Preorder\Model\Order\ProcessNew\IsOrderItemPreorderInterface;
use Amasty\Preorder\Model\Order\ProcessNew\SaveOrderItemFlagInterface;
use Amasty\Preorder\Model\Order\ProductQty;
use Amasty\Preorder\Model\OrderPreorder\Query\IsExistForOrder;
use Magento\Sales\Model\Order\Item as OrderItem;

class ProcessNew
{
    /**
     * @var ProductQty
     */
    private $productQty;

    /**
     * @var IsOrderItemPreorderInterface
     */
    private $isOrderItemPreorder;

    /**
     * @var SaveOrderItemFlagInterface
     */
    private $saveOrderItemFlag;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var IsExistForOrder
     */
    private $isExistForOrder;

    public function __construct(
        ProductQty $productQty,
        IsOrderItemPreorderInterface $isOrderItemPreorder,
        SaveOrderItemFlagInterface $saveOrderItemFlag,
        ConfigProvider $configProvider,
        IsExistForOrder $isExistForOrder
    ) {
        $this->productQty = $productQty;
        $this->isOrderItemPreorder = $isOrderItemPreorder;
        $this->saveOrderItemFlag = $saveOrderItemFlag;
        $this->configProvider = $configProvider;
        $this->isExistForOrder = $isExistForOrder;
    }

    public function beforeAfterSave(OrderItem $orderItem): void
    {
        if ($this->configProvider->isEnabled() && !$this->isExistForOrder->execute((int) $orderItem->getOrderId())) {
            $this->productQty->addBackorderedQty(
                (int)$orderItem->getProductId(),
                (float)$orderItem->getQtyBackordered()
            );

            $orderItemIsPreorder = $this->isOrderItemPreorder->execute($orderItem);
            if ($orderItemIsPreorder) {
                $this->saveOrderItemFlag->execute($orderItem);
            }
        }
    }
}
