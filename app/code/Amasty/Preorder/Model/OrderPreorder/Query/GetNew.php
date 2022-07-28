<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\OrderPreorder\Query;

use Amasty\Preorder\Api\Data\OrderInformationInterface;
use Amasty\Preorder\Api\Data\OrderInformationInterfaceFactory;

class GetNew implements GetNewInterface
{
    /**
     * @var OrderInformationInterface
     */
    private $orderInformationFactory;

    public function __construct(OrderInformationInterfaceFactory $orderInformationFactory)
    {
        $this->orderInformationFactory = $orderInformationFactory;
    }

    public function execute(array $data = []): OrderInformationInterface
    {
        $orderInformation = $this->orderInformationFactory->create();
        $orderInformation->addData($data);
        return $orderInformation;
    }
}
