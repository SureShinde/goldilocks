<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Ui\Component\Form\Channel\AdditionalDataProviders\OrderLimit;

use Amasty\DeliveryDateManager\Model\ResourceModel\OrderLimit\Collection;
use Amasty\DeliveryDateManager\Model\ResourceModel\OrderLimit\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * @method Collection getCollection()
 */
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

        $this->prepareCollection();
    }

    public function prepareCollection(): void
    {
        $this->getCollection()->addFieldToFilter('name', ['neq' => 'NULL']);
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->getCollection()->getData();
    }
}
