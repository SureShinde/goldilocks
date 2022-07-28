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

namespace Plumrocket\PrivateSale\Model\Event;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Customer\Model\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Plumrocket\PrivateSale\Api\Data\EventInterface;
use Plumrocket\PrivateSale\Helper\Config;
use Plumrocket\PrivateSale\Model\Integration\PopupLogin;

/**
 * Utils class for event url and params for tag <a>
 *
 * @since 5.0.0
 */
class Link
{
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    /**
     * @var \Plumrocket\PrivateSale\Model\Integration\PopupLogin
     */
    private $popupLogin;

    /**
     * @var \Plumrocket\PrivateSale\Model\Event\Status
     */
    private $eventStatus;

    /**
     * @param \Magento\Framework\App\Http\Context                  $httpContext
     * @param \Plumrocket\PrivateSale\Model\Integration\PopupLogin $popupLogin
     * @param \Plumrocket\PrivateSale\Model\Event\Status           $eventStatus
     */
    public function __construct(
        HttpContext $httpContext,
        PopupLogin $popupLogin,
        Status $eventStatus
    ) {
        $this->httpContext = $httpContext;
        $this->popupLogin = $popupLogin;
        $this->eventStatus = $eventStatus;
    }

    /**
     * @param \Plumrocket\PrivateSale\Api\Data\EventInterface $event
     * @return bool
     */
    public function isAvailable(EventInterface $event): bool
    {
        return $this->eventStatus->isUpcoming($event)
            ? $event->canMakeActionBeforeEventStarts(Config::BROWSING_EVENT)
            : true;
    }

    /**
     * @param \Plumrocket\PrivateSale\Api\Data\EventInterface $event
     * @param int|null                                        $customerGroupId
     * @return bool
     */
    public function isLocked(EventInterface $event, int $customerGroupId = null): bool
    {
        if (null === $customerGroupId) {
            $customerGroupId = $this->getCustomerGroupId();
        }

        return $event->isEventPrivate()
            && ! $event->canCustomerGroupMakeActionOnPrivateSale(
                Config::BROWSING_EVENT,
                $customerGroupId
            );
    }

    /**
     * @param \Plumrocket\PrivateSale\Api\Data\EventInterface|\Plumrocket\PrivateSale\Model\Event $event
     * @return string
     */
    public function getLink(EventInterface $event): string
    {
        return $this->getCatalogEntityUrl($event) ?: '#';
    }

    /**
     * @param \Plumrocket\PrivateSale\Api\Data\EventInterface $event
     * @return string
     */
    public function getCatalogEntityUrl(EventInterface $event): string
    {
        if ($catalogEntity = $event->getCatalogEntity()) {
            if ($catalogEntity instanceof ProductInterface) {
                return (string) $catalogEntity->getProductUrl();
            }

            if ($catalogEntity instanceof CategoryInterface) {
                return (string) $catalogEntity->getUrl();
            }
        }

        return '';
    }

    /**
     * @param \Plumrocket\PrivateSale\Api\Data\EventInterface $event
     * @param int|null                                        $customerGroupId
     * @return bool
     */
    public function showPopupLogin(EventInterface $event, int $customerGroupId = null): bool
    {
        return $this->isLocked($event, $customerGroupId) && $this->popupLogin->isActive($event);
    }

    /**
     * @param \Plumrocket\PrivateSale\Api\Data\EventInterface|\Plumrocket\PrivateSale\Model\Event $event
     * @return string
     */
    public function getPopupLoginAttributesHtml(EventInterface $event): string
    {
        return $this->showPopupLogin($event) ? "data-form=\"{$this->getPopupFormType($event)}\"" : '';
    }

    /**
     * @param \Plumrocket\PrivateSale\Api\Data\EventInterface|\Plumrocket\PrivateSale\Model\Event $event
     * @return string
     */
    public function getPopupLoginClass(EventInterface $event): string
    {
        return $this->showPopupLogin($event) ? 'show_popup_login' : '';
    }

    /**
     * @return int
     */
    private function getCustomerGroupId(): int
    {
        return (int) $this->httpContext->getValue(Context::CONTEXT_GROUP);
    }

    /**
     * @param \Plumrocket\PrivateSale\Api\Data\EventInterface $event
     * @return string
     */
    public function getPopupFormType(EventInterface $event): string
    {
        return $this->popupLogin->getFormType($event->getPrivateSaleLandingPage());
    }
}
