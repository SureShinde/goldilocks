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
 * @package     Plumrocket_PrivateSale
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Block\Event;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;
use Plumrocket\PrivateSale\Api\Data\EventInterface;
use Plumrocket\PrivateSale\Helper\Data;
use Plumrocket\PrivateSale\Helper\Timer as TimerHelper;
use Plumrocket\PrivateSale\Model\CurrentDateTime;
use Plumrocket\PrivateSale\Model\Event;
use Plumrocket\PrivateSale\Model\Event\Link as EventLink;

/**
 * @method $this setEvent(EventInterface $event)
 * @method $this setComingSoon(bool $flag)
 *
 * @since v5.0.0
 */
class Item extends Template implements IdentityInterface
{
    /**
     * @var \Plumrocket\PrivateSale\Helper\Data
     */
    private $dataHelper;

    /**
     * @var TimerHelper
     */
    private $timerHelper;

    /**
     * @var CurrentDateTime
     */
    private $currentDateTime;

    /**
     * @var EventLink
     */
    private $eventLink;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Plumrocket\PrivateSale\Helper\Data              $dataHelper
     * @param \Plumrocket\PrivateSale\Helper\Timer             $timerHelper
     * @param \Plumrocket\PrivateSale\Model\CurrentDateTime    $currentDateTime
     * @param \Plumrocket\PrivateSale\Model\Event\Link         $eventLink
     * @param array                                            $data
     */
    public function __construct(
        Template\Context $context,
        Data $dataHelper,
        TimerHelper $timerHelper,
        CurrentDateTime $currentDateTime,
        EventLink $eventLink,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataHelper = $dataHelper;
        $this->timerHelper = $timerHelper;
        $this->currentDateTime = $currentDateTime;
        $this->eventLink = $eventLink;
    }

    /**
     * Is countdown must be shown
     *
     * @param EventInterface
     * @return boolean
     */
    public function showCountdown($event)
    {
        return $this->getTimerLayout() !== TimerHelper::DISABLED;
    }

    /**
     * @return \Plumrocket\PrivateSale\Model\Event\Link
     */
    public function getEventLinkModel(): EventLink
    {
        return $this->eventLink;
    }

    /**
     * @param EventInterface $event
     * @param bool $forVideoPlaceholder
     * @return string
     */
    public function getEventImage(EventInterface $event, bool $forVideoPlaceholder = false)
    {
        $imageUrl = '';

        if ($event->getImage()) {
            $imageUrl = $event->getImageUrl($event->getImage());
        } elseif (! $forVideoPlaceholder) {
            $imageUrl = $this->getViewFileUrl('Plumrocket_PrivateSale::images/default.jpg');
        }

        return $imageUrl;
    }

    /**
     * @param EventInterface $event
     * @return string
     */
    public function getSmallImage(EventInterface $event)
    {
        return $event->getSmallImage()
            ? $event->getImageUrl($event->getSmallImage())
            : $this->getEventImage($event);
    }

    /**
     * @param EventInterface $event
     * @return string
     */
    public function getVideoId(EventInterface $event)
    {
        $eventVideo = $event->getVideo();

        return isset($eventVideo[0]['video_url']) ? $this->dataHelper->getVideoId($eventVideo[0]['video_url']) : '';
    }

    /**
     * @return EventInterface|null
     */
    public function getEvent()
    {
        return $this->_getData('event') ?: $this->getItem();
    }

    /**
     * @inheritDoc
     */
    public function getIdentities()
    {
        return $this->getEvent() ? $this->getEvent()->getIdentities() : [Event::CACHE_TAG];
    }

    /**
     * @param $event
     * @return string
     * @throws \Exception
     */
    public function uniqId(EventInterface $event): string
    {
        return $event->getId() . random_int(100, 999);
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTimerLabel()
    {
        return $this->timerHelper->getTimerLabel($this->getTimerLayout());
    }

    /**
     * @return string
     */
    public function getTimerLayout()
    {
        return $this->timerHelper->getPageTimerFormat(TimerHelper::HOMEPAGE);
    }

    /**
     * @return bool
     */
    public function isStaticDate()
    {
        return $this->getTimerLayout() === TimerHelper::STATIC_DATE;
    }

    /**
     * @param $date
     * @return string
     */
    public function staticDateFormat($date)
    {
        return $this->currentDateTime->convertToCurrentTimezone($date)
            ->format(TimerHelper::STATIC_DATE_FORMAT);
    }
}
