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
 * @copyright   Copyright (c) 2020 Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Block\Integration;

use Magento\Framework\View\Element\Template;
use Plumrocket\PrivateSale\Model\Integration\PopupLogin as PopupLoginModel;
use Plumrocket\PrivateSale\Model\Integration\EventData;

class PopupLogin extends Template
{
    /**
     * @var PopupLoginModel
     */
    private $popuplogin;

    /**
     * @var EventData
     */
    private $eventData;

    /**
     * PopupLogin constructor.
     * @param EventData $eventData
     * @param PopupLoginModel $popuplogin
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        EventData $eventData,
        PopupLoginModel $popuplogin,
        Template\Context $context,
        array $data = []
    ) {
        $this->eventData = $eventData;
        $this->popuplogin = $popuplogin;

        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    private function isEvent(): bool
    {
        return $this->eventData->canBrowsingPrivateEvent();
    }

    /**
     * @return \Plumrocket\PrivateSale\Api\Data\EventInterface|null
     */
    private function getCurrentEvent()
    {
        return $this->eventData->getCurrentEvent();
    }

    /**
     * @return string
     */
    public function typeOfPopupForm() : string
    {
        if ($event = $this->getCurrentEvent()) {
            return $event->getPrivateSaleLandingPage();
        }

        return '';
    }

    /**
     * @return string
     */
    protected function _toHtml() : string
    {
        if ($this->popuplogin->isReady() && $this->isEvent()) {
            return parent::_toHtml();
        }

        return '';
    }
}
