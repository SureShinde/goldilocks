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

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Plumrocket\PrivateSale\Api\EventRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class GetCurrentEventService
{
    const SESSION_NAME = 'event_id';

    const QUERY_PARAM = 'event_id';

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    /**
     * @var int
     */
    private $eventId;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var \Plumrocket\PrivateSale\Api\Data\EventInterface|null
     */
    private $currentEvent;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * GetCurrentEventService constructor.
     *
     * @param SessionManagerInterface $sessionManager
     * @param EventRepositoryInterface $eventRepository
     * @param RequestInterface $request
     */
    public function __construct(
        SessionManagerInterface $sessionManager,
        EventRepositoryInterface $eventRepository,
        RequestInterface $request
    ) {
        $this->sessionManager = $sessionManager;
        $this->eventRepository = $eventRepository;
        $this->request = $request;
    }

    /**
     * @return \Plumrocket\PrivateSale\Api\Data\EventInterface|null
     */
    public function getEvent()
    {
        if (! $this->currentEvent) {
            $eventId = $this->getEventId();

            if (! $eventId) {
                return null;
            }

            try {
                $this->currentEvent = $this->eventRepository->getById($eventId);
            } catch (NoSuchEntityException $e) {
                return null;
            }
        }

        return $this->currentEvent;
    }

    /**
     * @return int
     */
    public function getEventId()
    {
        if (null === $this->eventId) {
            $currentEventId = $this->sessionManager->getData(self::SESSION_NAME);

            if (! $currentEventId) {
                $currentEventId = $this->request->getParam(self::QUERY_PARAM);
            } else {
                $this->sessionManager->unsetData(self::SESSION_NAME);
            }

            $this->eventId = (int) $currentEventId;
        }

        return $this->eventId;
    }
}
