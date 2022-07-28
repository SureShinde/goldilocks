<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\OrderItemPreorder\Query;

use Amasty\Preorder\Api\Data\OrderItemInformationInterface;
use Amasty\Preorder\Api\Data\OrderItemInformationInterfaceFactory;

class GetNew implements GetNewInterface
{
    /**
     * @var OrderItemInformationInterfaceFactory
     */
    private $orderItemInformationFactory;

    public function __construct(OrderItemInformationInterfaceFactory $orderItemInformationFactory)
    {
        $this->orderItemInformationFactory = $orderItemInformationFactory;
    }

    public function execute(array $data = []): OrderItemInformationInterface
    {
        $orderItemInformation = $this->orderItemInformationFactory->create();
        $orderItemInformation->addData($data);
        return $orderItemInformation;
    }
}
