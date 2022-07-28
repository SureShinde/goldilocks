<?php

namespace Amasty\Storelocator\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Gallery
 */
class Gallery extends AbstractModel
{
    public function _construct()
    {
        $this->_init(ResourceModel\Gallery::class);
    }
}
