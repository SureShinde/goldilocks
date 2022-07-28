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

namespace Plumrocket\PrivateSale\Model\Frontend;

use Magento\Framework\Exception\NoSuchEntityException;
use Plumrocket\PrivateSale\Api\GetEventIdByCategoryIdInterface;
use Plumrocket\PrivateSale\Helper\Config;
use Plumrocket\PrivateSale\Model\Config\Source\EventStatus;
use Plumrocket\PrivateSale\Model\EventRepository;

class CategoryEventPermissions
{
    const ALLOW_BROWSING_CATEGORY = true;

    /**
     * @var EventRepository
     */
    private $eventsRepository;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var bool[]
     */
    private $calculatedPermission = [];

    /**
     * @var \Plumrocket\PrivateSale\Api\GetEventIdByCategoryIdInterface
     */
    private $getEventIdByCategoryId;

    /**
     * @param \Plumrocket\PrivateSale\Model\EventRepository               $eventsRepository
     * @param \Plumrocket\PrivateSale\Helper\Config                       $config
     * @param \Plumrocket\PrivateSale\Api\GetEventIdByCategoryIdInterface $getEventIdByCategoryId
     */
    public function __construct(
        EventRepository $eventsRepository,
        Config $config,
        GetEventIdByCategoryIdInterface $getEventIdByCategoryId
    ) {
        $this->eventsRepository = $eventsRepository;
        $this->config = $config;
        $this->getEventIdByCategoryId = $getEventIdByCategoryId;
    }

    /**
     * @param int $categoryId
     * @return bool
     */
    public function canBrowsing(int $categoryId): bool
    {
        if (! isset($this->calculatedPermission[$categoryId])) {
            $this->calculatedPermission[$categoryId] = $this->calculateBrowsingPermission($categoryId);
        }

        return $this->calculatedPermission[$categoryId];
    }

    /**
     * @param int $categoryId
     * @return bool
     */
    private function calculateBrowsingPermission(int $categoryId): bool
    {
        if (! $this->config->isModuleEnabled()) {
            return self::ALLOW_BROWSING_CATEGORY;
        }

        $activeEventId = $this->getEventIdByCategoryId->execute($categoryId);
        if ($activeEventId && $activeEvent = $this->getEvent($activeEventId)) {
            if ($activeEvent->isEventPrivate()) {
                return $activeEvent->canCustomerGroupMakeActionOnPrivateSale(Config::BROWSING_EVENT);
            }
            return self::ALLOW_BROWSING_CATEGORY;
        }

        $upcomingEventId = $this->getEventIdByCategoryId->execute($categoryId, EventStatus::UPCOMING);
        if ($upcomingEventId && $activeEvent = $this->getEvent($upcomingEventId)) {
            return $activeEvent->canMakeActionBeforeEventStarts(Config::BROWSING_EVENT);
        }

        $endedEventId = $this->getEventIdByCategoryId->execute($categoryId, EventStatus::ENDED);
        if ($endedEventId && $activeEvent = $this->getEvent($endedEventId)) {
            return $activeEvent->canMakeActionAfterEventEnds(Config::BROWSING_EVENT);
        }

        return self::ALLOW_BROWSING_CATEGORY;
    }

    /**
     * @param int $eventId
     * @return \Plumrocket\PrivateSale\Api\Data\EventInterface|null
     */
    private function getEvent(int $eventId)
    {
        try {
            return $this->eventsRepository->getById($eventId);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }
}
