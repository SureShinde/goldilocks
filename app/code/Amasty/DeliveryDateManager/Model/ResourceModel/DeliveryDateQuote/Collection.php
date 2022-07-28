<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryDateQuote;

use Amasty\DeliveryDateManager\Model\ResourceModel\AbstractTypifiedCollection;

/**
 * @method \Amasty\DeliveryDateManager\Model\DeliveryQuote\DeliveryDateQuoteData[] getItems()
 */
class Collection extends AbstractTypifiedCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'delivery_quote_id';

    protected function _construct()
    {
        $this->_init(
            \Amasty\DeliveryDateManager\Model\DeliveryQuote\DeliveryDateQuoteData::class,
            \Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryDateQuote::class
        );
    }
}
