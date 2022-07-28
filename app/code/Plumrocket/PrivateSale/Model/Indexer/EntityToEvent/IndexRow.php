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

namespace Plumrocket\PrivateSale\Model\Indexer\EntityToEvent;

use Plumrocket\PrivateSale\Model\Config\Source\EventStatus;
use Plumrocket\PrivateSale\Model\Config\Source\EventType;
use Plumrocket\PrivateSale\Model\CurrentDateTime;

/**
 * Utils class for index row
 *
 * @since 5.0.0
 */
class IndexRow
{
    /**
     * @var \Plumrocket\PrivateSale\Model\CurrentDateTime
     */
    private $currentDateTime;

    /**
     * @var array
     */
    private $rowData;

    /**
     * @param \Plumrocket\PrivateSale\Model\CurrentDateTime $currentDateTime
     * @param array                                         $rowData
     */
    public function __construct(CurrentDateTime $currentDateTime, array $rowData)
    {
        $this->currentDateTime = $currentDateTime;
        $this->rowData = $rowData;
    }

    public function getEventId(): int
    {
        return (int) $this->rowData[Structure::EVENT_ID];
    }

    public function getCatalogEntityId(): int
    {
        return (int) $this->rowData[Structure::ENTITY_ID];
    }

    public function getWebsiteId(): int
    {
        return (int) $this->rowData[Structure::WEBSITE_ID];
    }

    public function getType(): int
    {
        return (int) $this->rowData[Structure::TYPE];
    }

    public function isPrivate(): bool
    {
        return (bool) $this->rowData[Structure::IS_PRIVATE];
    }

    public function getPriority(): int
    {
        return (int) $this->rowData[Structure::PRIORITY];
    }

    public function getEventActiveFrom(): string
    {
        return (string) $this->rowData[Structure::EVENT_FROM];
    }

    public function getEventActiveTo(): string
    {
        return (string) $this->rowData[Structure::EVENT_TO];
    }

    /**
     * @return int
     */
    public function getSimpleStatus(): int
    {
        if ($this->isActive()) {
            return EventStatus::ACTIVE;
        }

        if ($this->isEnded()) {
            return EventStatus::ENDED;
        }

        return EventStatus::UPCOMING;
    }

    public function isProductEvent(): bool
    {
        return $this->getType() === EventType::PRODUCT;
    }

    public function isActive(): bool
    {
        return $this->isBegan() && ! $this->isEnded();
    }

    public function isBegan(): bool
    {
        return $this->getEventActiveFrom() < $this->currentDateTime->getCurrentGmtDate();
    }

    public function isEnded(): bool
    {
        return $this->getEventActiveTo() < $this->currentDateTime->getCurrentGmtDate();
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->rowData;
    }
}
