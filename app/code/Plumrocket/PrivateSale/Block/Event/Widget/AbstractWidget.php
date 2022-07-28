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

namespace Plumrocket\PrivateSale\Block\Event\Widget;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManager;
use Magento\Widget\Block\BlockInterface;
use Plumrocket\PrivateSale\Api\Data\EventInterface;
use Plumrocket\PrivateSale\Block\Event\Item;
use Plumrocket\PrivateSale\Block\Homepage as EventHomepageBlock;
use Plumrocket\PrivateSale\Helper\Config;
use Plumrocket\PrivateSale\ViewModel\Event;

/**
 * @method null|bool getExcludeEndingSoon()
 *
 * @since v5.0.0
 */
abstract class AbstractWidget extends Template implements BlockInterface
{
    /**
     * @var null|EventInterface[]
     */
    protected $events;

    /**
     * Default event item block
     * @var string
     */
    protected $defaultEventItemBlock = 'homepage.event.item';

    /**
     * Block title
     * @var string
     */
    protected $blockTitle = '';

    /**
     * Store manager
     * @var \Magento\Store\Model\StoreManager
     */
    protected $storeManager;

    /**
     * Registry
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Config Helper
     * @var \Plumrocket\PrivateSale\Helper\Config
     */
    protected $config;

    /**
     * Current category
     * @var object
     */
    protected $currentCategory;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var Event
     */
    protected $eventViewModel;

    /**
     * AbstractHomepage constructor.
     *
     * @param \Magento\Framework\Registry                      $registry
     * @param \Magento\Store\Model\StoreManager                $storeManager
     * @param \Plumrocket\PrivateSale\Helper\Config            $config
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Plumrocket\PrivateSale\ViewModel\Event          $eventViewModel
     * @param array                                            $data
     */
    public function __construct(
        Registry $registry,
        StoreManager $storeManager,
        Config $config,
        Context $context,
        CategoryRepositoryInterface $categoryRepository,
        Event $eventViewModel,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
        $this->eventViewModel = $eventViewModel;
        $this->config = $config;

        parent::__construct($context, $data);
    }

    /**
     * @return \Plumrocket\PrivateSale\Api\Data\EventInterface[]
     */
    abstract public function getEvents(): array;

    /**
     * Retrieve item html
     *
     * @param \Plumrocket\PrivateSale\Api\Data\EventInterface $event
     * @return string
     */
    public function getEventHtml(EventInterface $event): string
    {
        $block = $this->getEventBlock();
        return $block ? $block->setEvent($event)->toHtml() : '';
    }

    /**
     * Retrieve block title
     *
     * @return string|\Magento\Framework\Phrase
     */
    public function getBlockTitle()
    {
        return $this->blockTitle ?: __($this->getData('block_title'))->getText();
    }

    /**
     * Retrieve coming soon days from parent block
     * @return int
     */
    public function getComingSoonDays(): int
    {
        return (int) $this->getData('coming_soon_days') ?: $this->config->getComingSoonDays();
    }

    /**
     * Retrieve ending soon days from parent block or from data
     * @return int
     */
    public function getEndingSoonDays(): int
    {
        return (int) $this->getData('ending_soon_days') ?: $this->config->getEndingSoonDays();
    }

    /**
     * @return $this
     */
    public function setEventsExist()
    {
        $parent = $this->getParentBlock();

        if ($parent && $parent instanceof EventHomepageBlock) {
            $parent->setEventsExist();
        }

        return $this;
    }

    /**
     * Retrieve category
     * @return CategoryInterface
     */
    protected function getCategory()
    {
        try {
            if ($this->currentCategory === null) {
                if ($this->getCategoryId()) {
                    $this->currentCategory = $this->categoryRepository->get($this->getCategoryId());
                } elseif ($this->registry->registry('current_category')) {
                    $this->currentCategory = $this->registry->registry('current_category');
                } else {
                    $categoryId = $this->storeManager->getStore()->getRootCategoryId();
                    $this->currentCategory = $this->categoryRepository->get($categoryId);
                }
            }
        } catch (NoSuchEntityException $e) {
            return null;
        }

        return $this->currentCategory;
    }

    /**
     * Retrieve item block
     * @return string
     */
    protected function getEventItemBlock()
    {
        if ($this->getData('event_item_block')) {
            return $this->getData('event_item_block');
        }

        return $this->defaultEventItemBlock;
    }

    /**
     * @return \Plumrocket\PrivateSale\Block\Event\Item|null
     */
    protected function getEventBlock()
    {
        $itemBlock = $this->getLayout()->getBlock($this->getEventItemBlock());

        if (! $itemBlock) {
            /** @var \Plumrocket\PrivateSale\Block\Event\Item $itemBlock */
            $itemBlock = $this->getLayout()->createBlock(Item::class);
            $itemBlock->setTemplate('Plumrocket_PrivateSale::homepage/event/item/default.phtml');
        }

        return $itemBlock;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (! $this->config->isModuleEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Put events which is end sooner at the beginning
     *
     * @param array $events
     * @return array
     */
    protected function sortEventsByEndTime(array $events): array
    {
        usort($events, static function (EventInterface $event1, EventInterface $event2) {
            if ($event1->getActiveTo() === $event2->getActiveTo()) {
                return 0;
            }

            return $event1->getActiveTo() > $event2->getActiveTo() ? 1 : -1;
        });

        return $events;
    }

    /**
     * Put events which is end sooner at the beginning
     *
     * @param array $events
     * @return array
     */
    protected function sortEventsByStartTime(array $events): array
    {
        usort($events, static function (EventInterface $event1, EventInterface $event2) {
            if ($event1->getActiveFrom() === $event2->getActiveFrom()) {
                return 0;
            }

            return $event1->getActiveFrom() > $event2->getActiveFrom() ? 1 : -1;
        });

        return $events;
    }

    /**
     * @deprecated since v5.0.0
     * @see \Plumrocket\PrivateSale\Block\Event\Widget\AbstractWidget::getEvents
     * @return array
     */
    public function getItems()
    {
        return [['items' => $this->getEvents()]];
    }

    /**
     * @deprecated since v5.0.0
     * @see \Plumrocket\PrivateSale\Block\Event\Widget\AbstractWidget::getEventHtml
     *
     * @param \Plumrocket\PrivateSale\Api\Data\EventInterface $event
     * @return string
     */
    public function getItemHtml($event)
    {
        return $this->getEventHtml($event);
    }

    /**
     * @deprecated since v5.0.0
     * @see \Plumrocket\PrivateSale\Block\Event\Widget\AbstractWidget::getEventBlock
     *
     * @return \Plumrocket\PrivateSale\Block\Event\Item|null
     */
    protected function getItemBlock()
    {
        return $this->getEventBlock();
    }
}
