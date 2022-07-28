<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

abstract class AbstractTypifiedCollection extends AbstractCollection
{
    /**
     * Strongly typified version of getData.
     * Can be done only without joined data.
     *
     * @return array
     */
    public function getTypifiedData(): array
    {
        $data = [];
        $item = $this->getNewEmptyItem();
        foreach ($this->getData() as $key => $itemData) {
            $item->setData($itemData);
            $data[$key] = $item->toArray();
        }

        return $data;
    }

    /**
     * Convert collection to array
     *
     * @param array $arrRequiredFields
     *
     * @return array
     */
    public function toArray($arrRequiredFields = [])
    {
        return [
            'totalRecords' => $this->getSize(),
            'items' => $this->getTypifiedData()
        ];
    }
}
