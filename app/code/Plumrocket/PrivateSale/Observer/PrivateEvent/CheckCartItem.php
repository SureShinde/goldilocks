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

namespace Plumrocket\PrivateSale\Observer\PrivateEvent;

use Plumrocket\PrivateSale\Helper\Config;

class CheckCartItem extends AbstractPermission
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (! $this->config->isModuleEnabled()) {
            return;
        }

        $product = $observer->getItem()->getProduct();
        $event = $this->eventCatalog->getEventForProduct($product);
        $groupId = $this->getCustomerGroupId();

        if ($event && $event->isEventPrivate()
            && ! $event->canCustomerGroupMakeActionOnPrivateSale(Config::SHOW_ADD_TO_CART, $groupId)
        ) {
            $observer->getItem()->addErrorInfo(
                'privatsales',
                \Magento\CatalogInventory\Helper\Data::ERROR_QTY,
                __('We can\'t add this item to your shopping cart right now.')
            );
        }
    }
}
