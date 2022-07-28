<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Ui\Component\Grid\Channel\Filter\Source;

use Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval\Set\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

class TimeSet implements OptionSourceInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        if ($this->options === null) {
            $collection = $this->collectionFactory->create();
            $options = [];

            foreach ($collection->getItems() as $timeSet) {
                $options[] = [
                    'value' => $timeSet->getId(),
                    'label' => $timeSet->getName()
                ];
            }
            $this->options = $options;
        }

        return $this->options;
    }
}
