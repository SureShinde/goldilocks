<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket Private Sales and Flash Sales
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Model\ResourceModel\FlashSale;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Plumrocket\PrivateSale\Api\Data\FlashSaleInterface;
use Plumrocket\PrivateSale\Model\FlashSale;
use Plumrocket\PrivateSale\Model\ResourceModel\FlashSale as FlashSaleResource;

/**
 * @method FlashSaleInterface getFirstItem()
 *
 * @since v5.0.0
 */
class Collection extends AbstractCollection
{
    protected $_idFieldName = 'sale_id';

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(
            FlashSale::class,
            FlashSaleResource::class
        );
    }

    /**
     * @param $fieldName
     * @return AbstractCollection
     */
    public function setIdFieldName($fieldName)
    {
        return parent::_setIdFieldName($fieldName);
    }
}
