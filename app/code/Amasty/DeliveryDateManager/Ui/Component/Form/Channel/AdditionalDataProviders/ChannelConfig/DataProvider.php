<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Ui\Component\Form\Channel\AdditionalDataProviders\ChannelConfig;

use Amasty\DeliveryDateManager\Api\Data\ChannelConfigDataInterface;
use Amasty\DeliveryDateManager\Model\ResourceModel\ChannelConfig\Collection;
use Amasty\DeliveryDateManager\Model\ResourceModel\ChannelConfig\CollectionFactory;
use Amasty\DeliveryDateManager\Model\TimeInterval\MinsToTimeConverter;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * @method Collection getCollection()
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var MinsToTimeConverter
     */
    private $minsToTimeConverter;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        MinsToTimeConverter $minsToTimeConverter,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->minsToTimeConverter = $minsToTimeConverter;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        $data = $this->getCollection()->getData();

        foreach ($data as &$row) {
            if (isset($row[ChannelConfigDataInterface::SAME_DAY_CUTOFF])) {
                $row[ChannelConfigDataInterface::SAME_DAY_CUTOFF] =
                    $this->minsToTimeConverter->execute((int)$row[ChannelConfigDataInterface::SAME_DAY_CUTOFF]);
            }
        }

        return $data;
    }
}
