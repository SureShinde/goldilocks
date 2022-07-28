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
 * @package     Plumrocket_magento2.3.4
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Block\Event\Widget;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManager;
use Plumrocket\PrivateSale\Api\Data\EventInterface;
use Plumrocket\PrivateSale\Helper\Config;
use Plumrocket\PrivateSale\Model\Catalog\Category\GetChildrenIds;
use Plumrocket\PrivateSale\ViewModel\Event;

class ComingSoon extends AbstractWidget
{
    /**
     * @var \Plumrocket\PrivateSale\Model\Catalog\Category\GetChildrenIds
     */
    private $getCategoryChildrenIds;

    /**
     * ComingSoon constructor.
     *
     * @param \Magento\Framework\Registry                           $registry
     * @param \Magento\Store\Model\StoreManager                     $storeManager
     * @param \Plumrocket\PrivateSale\Helper\Config                 $config
     * @param \Magento\Framework\View\Element\Template\Context      $context
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface      $categoryRepository
     * @param \Plumrocket\PrivateSale\ViewModel\Event               $eventViewModel
     * @param \Plumrocket\PrivateSale\Model\Catalog\Category\GetChildrenIds $getCategoryChildrenIds
     * @param array                                                 $data
     */
    public function __construct(
        Registry $registry,
        StoreManager $storeManager,
        Config $config,
        Context $context,
        CategoryRepositoryInterface $categoryRepository,
        Event $eventViewModel,
        GetChildrenIds $getCategoryChildrenIds,
        array $data = []
    ) {
        parent::__construct(
            $registry,
            $storeManager,
            $config,
            $context,
            $categoryRepository,
            $eventViewModel,
            $data
        );
        $this->getCategoryChildrenIds = $getCategoryChildrenIds;
    }

    /**
     * @inheritdoc
     */
    public function getEvents(): array
    {
        $category = $this->getCategory();

        if (! $category) {
            return [];
        }

        if ($this->events === null) {
            $this->events = [];

            /**
             * We get all subcategories (enabled and disabled)
             * because coming soon events may disable category depends on permission
             */
            $allChildIds = $this->getCategoryChildrenIds->execute($category, true, true);

            if ($allChildIds) {
                $events = $this->eventViewModel->getUpcomingEvents($this->getComingSoonDays(), $allChildIds);

                if (! empty($events)) {
                    $this->setEventsExist();
                    $this->events = $this->sortEventsByStartTime($events);
                }
            }
        }

        return $this->events;
    }

    /**
     * @inheritDoc
     */
    public function getEventHtml(EventInterface $event): string
    {
        $block = $this->getEventBlock();
        return $block ? $block->setEvent($event)->setComingSoon(true)->toHtml() : '';
    }
}
