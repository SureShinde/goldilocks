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
 * @copyright   Copyright (c) 2016 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\PrivateSale\Block;

use Magento\Framework\View\Element\Template;

class Homepage extends Template
{
    /**
     * Coming soon days
     * @var int
     */
    protected $comingSoonDays = 3;

    /**
     * Ending soon days
     * @var int
     */
    protected $endingSoonDays = false;

    /**
     * @var bool
     */
    protected $eventExists = false;

    /**
     * Coming soon days setter
     * @param int $days
     */
    public function setComingSoonDays($days)
    {
        $this->comingSoonDays = $days;
        return $this;
    }

    /**
     * Retrieve coming soon days
     * @return int;
     */
    public function getComingSoonDays()
    {
        return $this->comingSoonDays;
    }

    /**
     * Retrieve ending soon days
     * @return int
     */
    public function getEndingSoonDays()
    {
        return $this->endingSoonDays;
    }

    /**
     * Ending soon days setter
     * @param int $days
     */
    public function setEndingSoonDays($days)
    {
        $this->endingSoonDays = $days;
        return $this;
    }

    /**
     * Add events count
     * @param integer $count
     */
    public function isEventsExist()
    {
        return $this->eventExists;
    }

    /**
     * Retrieve eventes count
     * @return int
     */
    public function setEventsExist()
    {
        return $this->eventExists = true;
    }
}
