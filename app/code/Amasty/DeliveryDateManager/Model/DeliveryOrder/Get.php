<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryOrder;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateOrderInterface;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\DeliveryOrderDataFactory;
use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryDateOrder;
use Magento\Framework\Exception\NoSuchEntityException;

class Get
{
    /**
     * @var DeliveryDateOrderInterface[]
     */
    protected $storageByOrder = [];

    /**
     * @var DeliveryOrderData
     */
    private $deliverydateResource;

    /**
     * @var DeliveryOrderDataFactory
     */
    private $deliverydateFactory;

    public function __construct(
        DeliveryDateOrder $deliverydateResource,
        DeliveryOrderDataFactory $deliverydateFactory
    ) {
        $this->deliverydateResource = $deliverydateResource;
        $this->deliverydateFactory = $deliverydateFactory;
    }

    /**
     * @param int $orderId
     *
     * @return DeliveryDateOrderInterface
     * @throws NoSuchEntityException
     */
    public function getByOrderId(int $orderId): DeliveryDateOrderInterface
    {
        if (!isset($this->storageByOrder[$orderId])) {
            /** @var DeliveryDateOrderInterface $model */
            $model = $this->deliverydateFactory->create();
            $this->deliverydateResource->load($model, $orderId, 'order_id');
            if (!$model->getOrderId()) {
                throw new NoSuchEntityException(__('Delivery Date for specified order ID "%1" not found.', $orderId));
            }
            $this->storageByOrder[$orderId] = $model;
        }

        return $this->storageByOrder[$orderId];
    }
}
