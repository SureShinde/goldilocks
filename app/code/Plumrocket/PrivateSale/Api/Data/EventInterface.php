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

namespace Plumrocket\PrivateSale\Api\Data;

interface EventInterface
{
    const IDENTIFIER = 'entity_id';
    const EVENT_VIDEO = 'event_video';
    const EVENT_NAME = 'event_name';
    const EVENT_TYPE = 'event_type';
    const PRIORITY = 'priority';
    const IS_PRIVATE = 'is_event_private';
    const IS_ENABLED = 'enable';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const CATEGORY_EVENT = 'category_event';
    const PRODUCT_EVENT = 'product_event';
    const EVENT_FROM = 'event_from';
    const EVENT_TO = 'event_to';
    const EVENT_DESCRIPTION = 'event_description';
    const EVENT_IMAGE = 'event_image';
    const HEADER_IMAGE = 'header_image';

    /**
     * @return int
     */
    public function getId(): int;

    /**
     * Retrieve if event is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return int
     */
    public function getCategoryId(): int;

    /**
     * @return int
     */
    public function getProductId(): int;

    /**
     * @return array
     */
    public function getVideo(): array;

    /**
     * @return string
     */
    public function getActiveFrom(): string;

    /**
     * @return string
     */
    public function getActiveTo(): string;

    /**
     * The highest priority is number 0.
     *
     * @return int
     */
    public function getPriority(): int;

    /**
     * @return string
     */
    public function getImage(): string;

    /**
     * @return string
     */
    public function getHeaderImage(): string;

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @return int
     */
    public function getType(): int;

    /**
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * @return string
     */
    public function getUpdatedAt(): string;

    /**
     * @param string $name
     * @return EventInterface
     */
    public function setEventName(string $name): EventInterface;

    /**
     * @param int $type
     * @return EventInterface
     */
    public function setEventType(int $type): EventInterface;

    /**
     * @param int $priority
     * @return \Plumrocket\PrivateSale\Api\Data\EventInterface
     */
    public function setPriority(int $priority): EventInterface;

    /**
     * @param string $date
     * @return EventInterface
     */
    public function setCreatedAt(string $date): EventInterface;

    /**
     * @param string $date
     * @return EventInterface
     */
    public function setUpdatedAt(string $date): EventInterface;

    /**
     * @return bool
     */
    public function isEventPrivate(): bool;

    /**
     * @return bool
     */
    public function isProductEvent(): bool;

    /**
     * @return bool
     */
    public function isCategoryEvent(): bool;

    /**
     * @return string
     */
    public function getPrivateSaleLandingPage(): string;
}
