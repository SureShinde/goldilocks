<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Ui\Component\Form\Channel\Modal;

use Amasty\DeliveryDateManager\Api\Data\OrderLimitInterface;
use Amasty\DeliveryDateManager\Model\OrderLimit\Get;
use Amasty\DeliveryDateManager\Model\ResourceModel\OrderLimit\Collection;
use Amasty\DeliveryDateManager\Model\ResourceModel\OrderLimit\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * @method Collection getCollection()
 */
class OrderLimitDataProvider extends AbstractDataProvider
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Get
     */
    private $orderLimitGetter;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        RequestInterface $request,
        CollectionFactory $collectionFactory,
        Get $orderLimitGetter,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->request = $request;
        $this->orderLimitGetter = $orderLimitGetter;
        $this->collection = $collectionFactory->create();
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        if ($orderLimitId = (int)$this->request->getParam($this->getRequestFieldName())) {
            $orderLimit = $this->orderLimitGetter->execute($orderLimitId);
            $this->data[$orderLimitId] = [
                OrderLimitInterface::LIMIT_ID => $orderLimitId,
                OrderLimitInterface::NAME => $orderLimit->getName(),
                OrderLimitInterface::DAY_LIMIT => $orderLimit->getDayLimit(),
                OrderLimitInterface::INTERVAL_LIMIT => $orderLimit->getIntervalLimit()
            ];
        }

        return $this->data;
    }
}
