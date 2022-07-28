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

namespace Plumrocket\PrivateSale\Plugin\Integration;

use Plumrocket\PrivateSale\Model\Integration\EventData;
use Plumrocket\PrivateSale\Model\Integration\PopupLogin as PopupLoginModel;
use Plumrocket\PrivateSale\Helper\Config as ConfigHelper;

class Popuplogin
{
    /**
     * @var EventData
     */
    private $eventData;

    /**
     * @var PopupLoginModel
     */
    private $popupLogin;

    /**
     * @var ConfigHelper
     */
    private $config;

    /**
     * Popuplogin constructor.
     * @param EventData $eventData
     * @param PopupLoginModel $popupLogin
     * @param ConfigHelper $config
     */
    public function __construct(
        EventData $eventData,
        PopupLoginModel $popupLogin,
        ConfigHelper $config
    ) {
        $this->eventData = $eventData;
        $this->popupLogin = $popupLogin;
        $this->config = $config;
    }

    /**
     * @param \Plumrocket\Popuplogin\Block\Popuplogin $subject
     * @param $result
     * @return mixed
     */
    public function afterGetConfig(\Plumrocket\Popuplogin\Block\Popuplogin $subject, $result)
    {
        if ($this->config->isModuleEnabled()
            && $this->eventData->canBrowsingPrivateEvent()
            && $this->popupLogin->isActive($this->eventData->getCurrentEvent())
        ) {
            $result['general']['mode'] = 4; //set Manual Mode
            $result['general']['close'] = 0; //set Allow to Close Popup as No
        }

        return $result;
    }
}
