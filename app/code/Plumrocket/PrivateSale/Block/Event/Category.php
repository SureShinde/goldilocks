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
 * @package     Plumrocket Private Sales and Flash Sales v4.x.x
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Block\Event;

use Plumrocket\PrivateSale\Helper\Timer as TimerHelper;

class Category extends AbstractEvent
{
    /**
     * @var string
     */
    private $categoryTimer;

    /**
     * @var \Plumrocket\PrivateSale\Model\Event
     */
    private $event;

    /**
     * @inheritDoc
     */
    public function getEvent()
    {
        return $this->getEventByCategory();
    }

    /**
     * Retrieve current category
     * @return \Magento\Catalog\Model\Category
     */
    protected function getCurrentCategory()
    {
        if ($category = $this->getData('category')) {
            if (is_int($category)) {
                $category = $this->categoryRepository->get($category);
            }

            return $category;
        }

        return $this->registry->registry('current_category');
    }

    /**
     * Retrieve product id
     * @return int
     */
    public function getItemId()
    {
         return $this->getCurrentCategory()->getId();
    }

    /**
     * Retrieve event end time
     * @return int
     */
    public function getEventEnd()
    {
        $event = $this->getEventByCategory();

        return $event ? strtotime($event->getActiveTo()) : 0;
    }

    /**
     * @return \Plumrocket\PrivateSale\Api\Data\EventInterface|null
     */
    public function getEventByCategory()
    {
        if (! $this->event) {
            $this->event = $this->eventCatalog->getEventForCategory($this->getCurrentCategory());
        }

        return $this->event;
    }

    /**
     * @return bool
     */
    public function isStaticDate()
    {
        return $this->getTimerLayout() === TimerHelper::STATIC_DATE;
    }

    /**
     * @return string
     */
    public function getStaticDate()
    {
        return $this->formatStaticDate($this->getEventEnd());
    }

    /**
     * @return string
     */
    public function getTimerLayout(): string
    {
        if (! $this->categoryTimer) {
            $this->categoryTimer = $this->timerHelper->getPageTimerFormat(TimerHelper::EVENT_CATEGORY);
        }

        return $this->categoryTimer;
    }

    /**
     * @return string
     */
    public function getEventImage()
    {
        $event = $this->getEventByCategory();
        $image = $this->getViewFileUrl('Plumrocket_PrivateSale::images/default.jpg');

        if ($event) {
            if ($event->getHeaderImage()) {
                $image = $event->getImageUrl($event->getHeaderImage());
            } elseif ($event->getImage()) {
                $image = $event->getImageUrl($event->getImage());
            }
        }

        return $image;
    }

    /**
     * @return string
     */
    public function getEventName()
    {
        return $this->getEventByCategory()
            ? $this->getEventByCategory()->getName() : '';
    }

    /**
     * @return string
     */
    public function getEventDescription()
    {
        return $this->getEventByCategory()
            ? $this->getEventByCategory()->getDescription() : '';
    }

    /**
     * @param $template
     * @return bool
     */
    public function canShowHeader($template)
    {
        return $template === $this->eventHeaderStyle->getCategoryHeaderType();
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (! $this->getEventByCategory()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * @inheritDoc
     */
    protected function _prepareLayout()
    {
        $this->getLayout()->unsetElement('page.main.title');
        return parent::_prepareLayout();
    }
}
