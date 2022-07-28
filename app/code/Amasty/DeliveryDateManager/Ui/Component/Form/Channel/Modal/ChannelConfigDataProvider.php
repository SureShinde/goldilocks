<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Ui\Component\Form\Channel\Modal;

use Amasty\DeliveryDateManager\Api\Data\ChannelConfigDataInterface;
use Amasty\DeliveryDateManager\Model\ChannelConfig\Get;
use Amasty\DeliveryDateManager\Model\ResourceModel\ChannelConfig\Collection;
use Amasty\DeliveryDateManager\Model\ResourceModel\ChannelConfig\CollectionFactory;
use Amasty\DeliveryDateManager\Model\TimeInterval\MinsToTimeConverter;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * @method Collection getCollection()
 */
class ChannelConfigDataProvider extends AbstractDataProvider
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Get
     */
    private $channelConfigGetter;

    /**
     * @var MinsToTimeConverter
     */
    private $minsToTimeConverter;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        RequestInterface $request,
        CollectionFactory $collectionFactory,
        Get $channelConfigGetter,
        MinsToTimeConverter $minsToTimeConverter,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->request = $request;
        $this->channelConfigGetter = $channelConfigGetter;
        $this->collection = $collectionFactory->create();
        $this->minsToTimeConverter = $minsToTimeConverter;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        if ($configId = (int)$this->request->getParam($this->getRequestFieldName())) {
            $channelConfig = $this->channelConfigGetter->execute($configId);
            $this->data[$configId] = [
                ChannelConfigDataInterface::ID => $configId,
                ChannelConfigDataInterface::NAME => $channelConfig->getName(),
                ChannelConfigDataInterface::MIN => $channelConfig->getMin(),
                ChannelConfigDataInterface::MAX => $channelConfig->getMax(),
                ChannelConfigDataInterface::IS_SAME_DAY_AVAILABLE =>
                    (string)(int)$channelConfig->getIsSameDayAvailable(),
                ChannelConfigDataInterface::SAME_DAY_CUTOFF => $this->minsToTimeConverter->execute(
                    (int)$channelConfig->getSameDayCutoff()
                ),
                ChannelConfigDataInterface::ORDER_TIME => $channelConfig->getOrderTime(),
                ChannelConfigDataInterface::BACKORDER_TIME => $channelConfig->getBackorderTime()
            ];
        }

        return $this->data;
    }
}
