<?php
/**
 * @package     Plumrocket_PrivateSale
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license/  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Model\Event;

use Plumrocket\PrivateSale\Api\Data\EventInterface;
use Plumrocket\PrivateSale\Model\CurrentDateTime;

/**
 * @since 5.0.0
 */
class Status
{
    /**
     * @var \Plumrocket\PrivateSale\Model\CurrentDateTime
     */
    private $currentDateTime;

    /**
     * @param \Plumrocket\PrivateSale\Model\CurrentDateTime $currentDateTime
     */
    public function __construct(CurrentDateTime $currentDateTime)
    {
        $this->currentDateTime = $currentDateTime;
    }

    /**
     * @param \Plumrocket\PrivateSale\Api\Data\EventInterface $event
     * @return bool
     */
    public function isUpcoming(EventInterface $event): bool
    {
        return ! $this->isBegan($event);
    }

    /**
     * @param \Plumrocket\PrivateSale\Api\Data\EventInterface $event
     * @return bool
     */
    public function isActive(EventInterface $event): bool
    {
        return $this->isBegan($event) && ! $this->isEnded($event);
    }

    /**
     * @param \Plumrocket\PrivateSale\Api\Data\EventInterface $event
     * @return bool
     */
    public function isBegan(EventInterface $event): bool
    {
        return $event->getActiveFrom() < $this->currentDateTime->getCurrentGmtDate();
    }

    /**
     * @param \Plumrocket\PrivateSale\Api\Data\EventInterface $event
     * @return bool
     */
    public function isEnded(EventInterface $event): bool
    {
        return $event->getActiveTo() < $this->currentDateTime->getCurrentGmtDate();
    }
}
