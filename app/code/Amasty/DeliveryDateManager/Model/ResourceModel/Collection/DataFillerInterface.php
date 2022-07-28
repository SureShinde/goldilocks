<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel\Collection;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

interface DataFillerInterface
{
    /**
     * Attach related collection data
     *
     * @param AbstractCollection $collection
     * @return void
     */
    public function attachData(AbstractCollection $collection): void;
}
