<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Api\Data;

/**
 * Search results of getList method of TimeInterval
 */
interface TimeIntervalSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get source items list
     *
     * @return \Amasty\DeliveryDateManager\Api\Data\TimeIntervalInterface[]
     */
    public function getItems();

    /**
     * Set source items list
     *
     * @param \Amasty\DeliveryDateManager\Api\Data\TimeIntervalInterface[] $items
     * @return void
     */
    public function setItems(array $items);
}
