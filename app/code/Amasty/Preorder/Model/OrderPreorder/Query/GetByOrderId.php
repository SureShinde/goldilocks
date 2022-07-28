<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\OrderPreorder\Query;

use Amasty\Preorder\Api\Data\OrderInformationInterface;
use Amasty\Preorder\Api\Data\OrderInformationInterfaceFactory;
use Amasty\Preorder\Model\OrderPreorder;
use Amasty\Preorder\Model\ResourceModel\OrderPreorder as OrderPreorderResource;
use Magento\Framework\Exception\NoSuchEntityException;

class GetByOrderId implements GetByOrderIdInterface
{
    /**
     * @var array
     */
    private $cache = [];

    /**
     * @var OrderInformationInterfaceFactory
     */
    private $orderInformationFactory;

    /**
     * @var OrderPreorderResource
     */
    private $orderPreorderResource;

    public function __construct(
        OrderInformationInterfaceFactory $orderInformationFactory,
        OrderPreorderResource $orderPreorderResource
    ) {
        $this->orderInformationFactory = $orderInformationFactory;
        $this->orderPreorderResource = $orderPreorderResource;
    }

    /**
     * @param int $orderId
     * @return OrderInformationInterface
     * @throws NoSuchEntityException
     */
    public function execute(int $orderId): OrderInformationInterface
    {
        if (!isset($this->cache[$orderId])) {
            /** @var OrderInformationInterface|OrderPreorder $orderPreorder */
            $orderPreorder = $this->orderInformationFactory->create();
            $this->orderPreorderResource->load($orderPreorder, $orderId, OrderInformationInterface::ORDER_ID);
            if ($orderPreorder->getId() === null) {
                throw new NoSuchEntityException(
                    __('Order Preorder Information for Order with ID "%value" does not exist.', ['value' => $orderId])
                );
            }
            $this->cache[$orderId] = $orderPreorder;
        }

        return $this->cache[$orderId];
    }
}
