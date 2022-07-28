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

namespace Plumrocket\PrivateSale\Model;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Plumrocket\PrivateSale\Helper\Preview;

class CurrentDateTime
{
    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var Preview
     */
    private $previewHelper;

    /**
     * CurrentDateTime constructor.
     *
     * @param Preview $previewHelper
     * @param DateTime $dateTime
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        Preview $previewHelper,
        DateTime $dateTime,
        TimezoneInterface $timezone
    ) {
        $this->previewHelper = $previewHelper;
        $this->dateTime = $dateTime;
        $this->timezone = $timezone;
    }

    /**
     * @return \DateTime|false|string
     */
    public function getCurrentGmtDate()
    {
        $previewTimestamp = $this->getPreviewTimestamp();
        return $previewTimestamp ? date('Y-m-d H:i:s', $previewTimestamp) : $this->dateTime->gmtDate();
    }

    /**
     * @return float|int
     */
    public function getGmtTimestamp()
    {
        return $this->getPreviewTimestamp() ?: $this->dateTime->gmtTimestamp();
    }

    /**
     * @return \DateTime
     */
    public function getCurrentDate()
    {
        $previewTimestamp = $this->getPreviewTimestamp();
        return $this->timezone->date($previewTimestamp);
    }

    /**
     * @param $date
     * @return \DateTime
     */
    public function convertToCurrentTimezone($date)
    {
        return $this->timezone->date($date);
    }

    /**
     * @return float|int|null
     */
    private function getPreviewTimestamp()
    {
        if ($this->previewHelper->isAllow()) {
            $currentSeconds = $this->dateTime->date('H') * 3600
                + ((int) $this->dateTime->date('i') + 2) * 60;
            return $this->previewHelper->getPreviewTime() + $currentSeconds;
        }

        return null;
    }
}
