<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\OrderItemPreorder\Query;

use Amasty\Preorder\Api\Data\OrderItemInformationInterface;
use Amasty\Preorder\Api\Data\OrderItemInformationInterfaceFactory;
use Amasty\Preorder\Model\OrderItemPreorder;
use Amasty\Preorder\Model\ResourceModel\OrderItemPreorder as OrderItemPreorderResource;
use Magento\Framework\Exception\NoSuchEntityException;

class GetByItemId implements GetByItemIdInterface
{
    /**
     * @var array
     */
    private $cache = [];

    /**
     * @var OrderItemInformationInterfaceFactory
     */
    private $orderItemInformationFactory;

    /**
     * @var OrderItemPreorderResource
     */
    private $orderItemPreorderResource;

    public function __construct(
        OrderItemInformationInterfaceFactory $orderItemInformationFactory,
        OrderItemPreorderResource $orderItemPreorderResource
    ) {
        $this->orderItemInformationFactory = $orderItemInformationFactory;
        $this->orderItemPreorderResource = $orderItemPreorderResource;
    }

    /**
     * @param int $orderItemId
     * @return OrderItemInformationInterface
     * @throws NoSuchEntityException
     */
    public function execute(int $orderItemId): OrderItemInformationInterface
    {
        if (!isset($this->cache[$orderItemId])) {
            /** @var OrderItemInformationInterface|OrderItemPreorder $orderItemPreorder */
            $orderItemPreorder = $this->orderItemInformationFactory->create();
            $this->orderItemPreorderResource->load(
                $orderItemPreorder,
                $orderItemId,
                OrderItemInformationInterface::ORDER_ITEM_ID
            );

            if ($orderItemPreorder->getId() === null) {
                throw new NoSuchEntityException(
                    __(
                        'Order Item Preorder Information for Order with ID "%value" does not exist.',
                        ['value' => $orderItemId]
                    )
                );
            }
            $this->cache[$orderItemId] = $orderItemPreorder;
        }

        return $this->cache[$orderItemId];
    }
}
