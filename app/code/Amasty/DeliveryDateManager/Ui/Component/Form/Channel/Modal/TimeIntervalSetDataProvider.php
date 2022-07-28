<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Ui\Component\Form\Channel\Modal;

use Amasty\DeliveryDateManager\Api\Data\OrderLimitInterface;
use Amasty\DeliveryDateManager\Api\Data\TimeIntervalInterface;
use Amasty\DeliveryDateManager\Model\OrderLimit\Get as OrderLimitGetter;
use Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval\Collection;
use Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval\Set\CollectionFactory;
use Amasty\DeliveryDateManager\Model\TimeInterval\MinsToTimeConverter;
use Amasty\DeliveryDateManager\Model\TimeInterval\Provider as TimeIntervalProvider;
use Amasty\DeliveryDateManager\Model\TimeInterval\Set\Get;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * @method Collection getCollection()
 */
class TimeIntervalSetDataProvider extends AbstractDataProvider
{
    public const ROWS_KEY = 'custom_time_intervals';

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var TimeIntervalProvider
     */
    private $timesProvider;

    /**
     * @var OrderLimitGetter
     */
    private $orderLimitGetter;

    /**
     * @var Get
     */
    private $timeSetGetter;

    /**
     * @var MinsToTimeConverter
     */
    private $minsToTimeConverter;

    // @SuppressWarnings(PHPMD.ExcessiveParameterList)
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        RequestInterface $request,
        TimeIntervalProvider $timesProvider,
        CollectionFactory $collectionFactory,
        OrderLimitGetter $orderLimitGetter,
        Get $timeSetGetter,
        MinsToTimeConverter $minsToTimeConverter,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->request = $request;
        $this->timesProvider = $timesProvider;
        $this->orderLimitGetter = $orderLimitGetter;
        $this->timeSetGetter = $timeSetGetter;
        $this->minsToTimeConverter = $minsToTimeConverter;
        $this->collection = $collectionFactory->create();
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        if ($timeSetId = (int)$this->request->getParam($this->getRequestFieldName())) {
            $timeSet = $this->timeSetGetter->execute($timeSetId);
            $timesList = $this->timesProvider->getListByIds($timeSet->getTimeIds());
            $timeIntervals = [];

            foreach ($timesList->getItems() as $timeInterval) {
                $limitId = $timeInterval->getLimitId();

                $fromTime = $this->minsToTimeConverter->execute($timeInterval->getFrom());
                $toTime = $this->minsToTimeConverter->execute($timeInterval->getTo());

                $timeIntervals[] = [
                    TimeIntervalInterface::INTERVAL_ID => $timeInterval->getIntervalId(),
                    TimeIntervalInterface::FROM => $fromTime,
                    TimeIntervalInterface::TO => $toTime,
                    TimeIntervalInterface::LABEL => $timeInterval->getLabel(),
                    TimeIntervalInterface::POSITION => $timeInterval->getPosition(),
                    OrderLimitInterface::LIMIT_ID => $limitId,
                    OrderLimitInterface::INTERVAL_LIMIT => $this->getLimitValue($limitId)
                ];
            }

            $this->data[$timeSetId] = [
                'set_id' => $timeSetId,
                'name' => $timeSet->getName(),
                self::ROWS_KEY => $timeIntervals
            ];
        }

        return $this->data;
    }

    /**
     * @param int|null $limitId
     *
     * @return int|null
     */
    private function getLimitValue(?int $limitId): ?int
    {
        if (!$limitId) {
            return null;
        }

        return $this->orderLimitGetter->execute($limitId)->getIntervalLimit();
    }
}
