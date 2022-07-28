<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Ui\Component\Form\Sales\Order\Edit;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateOrderInterface;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\Get;
use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryDateOrder\Collection;
use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryDateOrder\CollectionFactory;
use Amasty\DeliveryDateManager\Model\TimeInterval\MinsToTimeConverter;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * @method Collection getCollection()
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Get
     */
    private $deliveryOrderGetter;

    /**
     * @var MinsToTimeConverter
     */
    private $minsToTimeConverter;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        RequestInterface $request,
        Get $deliveryOrderGetter,
        MinsToTimeConverter $minsToTimeConverter,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->request = $request;
        $this->deliveryOrderGetter = $deliveryOrderGetter;
        $this->minsToTimeConverter = $minsToTimeConverter;
        $this->collection = $collectionFactory->create();
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        $fromTime = $toTime = '';
        $orderId = (int)$this->request->getParam($this->getRequestFieldName());

        try {
            $deliveryOrder = $this->deliveryOrderGetter->getByOrderId($orderId);
        } catch (NoSuchEntityException $e) {
            $this->data[$orderId] = [DeliveryDateOrderInterface::ORDER_ID => $orderId];

            return $this->data;
        }

        $deliveryOrderId = $deliveryOrder->getDeliverydateId();
        if ($deliveryOrder->getTimeFrom()) {
            $fromTime = $this->minsToTimeConverter->execute($deliveryOrder->getTimeFrom());
        }
        if ($deliveryOrder->getTimeTo()) {
            $toTime = $this->minsToTimeConverter->execute($deliveryOrder->getTimeTo());
        }

        $this->data[$orderId] = [
            DeliveryDateOrderInterface::DELIVERYDATE_ID => $deliveryOrderId,
            DeliveryDateOrderInterface::ORDER_ID => $orderId,
            DeliveryDateOrderInterface::DATE => $deliveryOrder->getDate(),
            DeliveryDateOrderInterface::TIME_FROM => $fromTime,
            DeliveryDateOrderInterface::TIME_TO => $toTime,
            DeliveryDateOrderInterface::COMMENT => $deliveryOrder->getComment()
        ];

        return $this->data;
    }
}
