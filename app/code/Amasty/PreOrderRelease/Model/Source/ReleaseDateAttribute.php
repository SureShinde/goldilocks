<?php

declare(strict_types=1);

namespace Amasty\PreOrderRelease\Model\Source;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\OptionSourceInterface;

class ReleaseDateAttribute implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = [];

        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('frontend_input', 'date');
        $collection->addOrder('attribute_code', AbstractDb::SORT_ORDER_ASC);
        foreach ($collection->getItems() as $attribute) {
            $options[] = [
                'value' => $attribute->getAttributeCode(),
                'label' => $attribute->getAttributeCode()
            ];
        }

        return $options;
    }
}
