<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Ui\Component\Form\Channel\AdditionalDataProviders\TimeIntervalSet;

use Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval\Set\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider
{
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->getCollection()->getData();
    }
}
