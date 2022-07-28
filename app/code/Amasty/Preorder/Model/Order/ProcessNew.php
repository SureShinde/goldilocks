<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Order;

use Amasty\Preorder\Model\Order\Item\GetPreorderInformation as GetPreorderItemInformation;
use Amasty\Preorder\Model\Order\ProcessNew\SaveOrderFlagInterface;
use Magento\Sales\Api\Data\OrderInterface;

class ProcessNew
{
    /**
     * @var GetPreorderItemInformation
     */
    private $getPreorderInformation;

    /**
     * @var SaveOrderFlagInterface
     */
    private $saveOrderFlag;

    public function __construct(
        GetPreorderItemInformation $getPreorderInformation,
        SaveOrderFlagInterface $saveOrderFlag
    ) {
        $this->getPreorderInformation = $getPreorderInformation;
        $this->saveOrderFlag = $saveOrderFlag;
    }

    public function execute(OrderInterface $order): void
    {
        $orderIsPreorder = false;

        $itemCollection = $order->getItemsCollection();
        foreach ($itemCollection as $item) {
            $orderIsPreorder |= $this->getPreorderInformation->execute($item)->isPreorder();
        }

        if ($orderIsPreorder) {
            $this->saveOrderFlag->execute($order);
        }
    }
}
