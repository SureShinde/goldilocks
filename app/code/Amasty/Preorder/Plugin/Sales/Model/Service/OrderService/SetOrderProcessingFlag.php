<?php

declare(strict_types=1);

namespace Amasty\Preorder\Plugin\Sales\Model\Service\OrderService;

use Amasty\Preorder\Model\Order\OrderProcessingFlag;

class SetOrderProcessingFlag
{
    /**
     * @var OrderProcessingFlag
     */
    private $orderProcessingFlag;

    public function __construct(OrderProcessingFlag $orderProcessingFlag)
    {
        $this->orderProcessingFlag = $orderProcessingFlag;
    }

    public function beforePlace(): void
    {
        $this->orderProcessingFlag->setFlag(true);
    }
}
