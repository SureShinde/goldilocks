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

namespace Plumrocket\PrivateSale\Model\Integration;

use Plumrocket\PrivateSale\Helper\Config;
use Magento\Framework\Registry;
use Plumrocket\PrivateSale\Model\Event\Catalog as EventCatalog;

class EventData
{
    /**
     * @var null
     */
    private $currentEvent = null;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var EventCatalog
     */
    private $eventCatalog;

    /**
     * EventData constructor.
     * @param Registry $registry
     * @param EventCatalog $eventCatalog
     */
    public function __construct(
        Registry $registry,
        EventCatalog $eventCatalog
    ) {
        $this->registry = $registry;
        $this->eventCatalog = $eventCatalog;
    }

    /**
     * @return \Plumrocket\PrivateSale\Api\Data\EventInterface|null
     */
    public function getCurrentEvent()
    {
        if (null !== $this->currentEvent) {
            return $this->currentEvent;
        }

        $category = $this->registry->registry('current_category');

        if ($category && $category->getId()) {
            $this->currentEvent = $this->eventCatalog->getEventForCategory($category);
        } else {
            $product = $this->registry->registry('current_product');

            if ($product && $product->getId()) {
                $this->currentEvent = $this->eventCatalog->getEventForProduct($product);
            }
        }

        return $this->currentEvent;
    }

    /**
     * @return bool
     */
    public function canBrowsingPrivateEvent(): bool
    {
        $event = $this->getCurrentEvent();

        return $event && $event->isEventPrivate()
            && ! $event->canCustomerGroupMakeActionOnPrivateSale(Config::BROWSING_EVENT);
    }
}
