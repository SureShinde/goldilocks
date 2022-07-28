<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Api\Data;

/**
 * Search results of getList method of ChannelConfig
 */
interface ChannelConfigSearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * @return \Amasty\DeliveryDateManager\Api\Data\ChannelConfigDataInterface[]
     */
    public function getItems();

    /**
     * @param \Amasty\DeliveryDateManager\Api\Data\ChannelConfigDataInterface[] $items
     * @return void
     */
    public function setItems(array $items);
}
