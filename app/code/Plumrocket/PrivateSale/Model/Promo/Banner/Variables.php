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

namespace Plumrocket\PrivateSale\Model\Promo\Banner;

use Plumrocket\PrivateSale\Api\Data\EventInterface;
use Plumrocket\PrivateSale\Model\Event\Link;

/**
 * @since 5
 * .0.0
 */
class Variables
{
    /**
     * @var \Plumrocket\PrivateSale\Model\Event\Link
     */
    private $eventLink;

    /**
     * @param \Plumrocket\PrivateSale\Model\Event\Link $eventLink
     */
    public function __construct(Link $eventLink)
    {
        $this->eventLink = $eventLink;
    }

    /**
     * @param \Plumrocket\PrivateSale\Api\Data\EventInterface $event
     * @param string                                          $bannerHtml
     * @return string|string[]
     */
    public function apply(EventInterface $event, string $bannerHtml)
    {
        return str_replace(
            [
                '{{prprivatesale-event-url}}',
                '{{prprivatesale-popup-login-class}}',
                '{{prprivatesale-popup-login-prams}}',
            ],
            [
                $this->eventLink->getLink($event),
                $this->eventLink->getPopupLoginClass($event),
                $this->eventLink->showPopupLogin($event) ? $this->eventLink->getPopupFormType($event) : 'none',
            ],
            $bannerHtml
        );
    }
}
