<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Api\Data;

/**
 * Search results of getList method of DeliveryChannel
 */
interface DeliveryChannelSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get source items list
     *
     * @return \Amasty\DeliveryDateManager\Api\Data\DeliveryChannelInterface[]
     */
    public function getItems();

    /**
     * Set source items list
     *
     * @param \Amasty\DeliveryDateManager\Api\Data\DeliveryChannelInterface[] $items
     * @return void
     */
    public function setItems(array $items);

    /**
     * Get items entity IDs
     *
     * @return int[]
     */
    public function getIds(): array;
}
