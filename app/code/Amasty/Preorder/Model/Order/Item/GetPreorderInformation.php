<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Order\Item;

use Amasty\Preorder\Api\Data\OrderItemInformationInterface;
use Amasty\Preorder\Model\Order\OrderItemProcessor;
use Magento\Sales\Api\Data\OrderItemInterface;

class GetPreorderInformation
{
    /**
     * @var OrderItemProcessor
     */
    private $processor;

    public function __construct(OrderItemProcessor $processor)
    {
        $this->processor = $processor;
    }

    public function execute(OrderItemInterface $orderItem): OrderItemInformationInterface
    {
        if ($orderItem->getExtensionAttributes()->getPreorderInfo() === null) {
            $this->processor->execute([$orderItem]);
        }

        return $orderItem->getExtensionAttributes()->getPreorderInfo();
    }
}
