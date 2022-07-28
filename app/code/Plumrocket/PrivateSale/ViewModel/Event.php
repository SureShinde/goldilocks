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

namespace Plumrocket\PrivateSale\ViewModel;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Plumrocket\PrivateSale\Api\Data\EventInterface;
use Plumrocket\PrivateSale\Api\EventRepositoryInterface;
use Plumrocket\PrivateSale\Helper\Config;
use Plumrocket\PrivateSale\Helper\Preview;
use Plumrocket\PrivateSale\Model\Config\Source\EventStatus;
use Plumrocket\PrivateSale\Model\Event\Catalog as CatalogEvent;

class Event implements ArgumentInterface
{
    /**
     * @var Preview
     */
    protected $previewHelper;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Plumrocket\PrivateSale\Helper\Config
     */
    private $config;

    /**
     * @var CatalogEvent
     */
    private $catalogEvents;

    /**
     * @param \Plumrocket\PrivateSale\Api\EventRepositoryInterface $eventRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder         $searchCriteriaBuilder
     * @param \Plumrocket\PrivateSale\Helper\Preview               $previewHelper
     * @param \Plumrocket\PrivateSale\Helper\Config                $config
     * @param CatalogEvent                                         $catalogEvents
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Preview $previewHelper,
        Config $config,
        CatalogEvent $catalogEvents
    ) {
        $this->eventRepository = $eventRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->previewHelper = $previewHelper;
        $this->config = $config;
        $this->catalogEvents = $catalogEvents;
    }

    /**
     * @param int   $upcomingDays
     * @param array $onlyForCategoryIds
     * @return array|\Plumrocket\PrivateSale\Api\Data\EventInterface[]
     */
    public function getUpcomingEvents(int $upcomingDays, array $onlyForCategoryIds = []): array
    {
        $eventIds = $this->catalogEvents->getByCategories($onlyForCategoryIds, EventStatus::COMING_SOON, $upcomingDays);

        if (! $eventIds) {
            return [];
        }

        return $this->getItems($eventIds);
    }

    /**
     * @param int   $endingSoonDays
     * @param array $onlyForCategoryIds
     * @return \Plumrocket\PrivateSale\Api\Data\EventInterface[]
     */
    public function getEndingSoonEvents(int $endingSoonDays, array $onlyForCategoryIds = []): array
    {
        $eventIds = $this->catalogEvents->getByCategories(
            $onlyForCategoryIds,
            EventStatus::ENDING_SOON,
            $endingSoonDays
        );

        if (! $eventIds) {
            return [];
        }

        return $this->getItems($eventIds);
    }

    /**
     * @param array $onlyForCategoryIds
     * @param bool  $excludeEndingSoon
     * @return \Plumrocket\PrivateSale\Api\Data\EventInterface[]
     */
    public function getActiveEvents(array $onlyForCategoryIds = [], bool $excludeEndingSoon = false): array
    {
        $eventIds = $this->catalogEvents->getByCategories(
            $onlyForCategoryIds,
            EventStatus::ACTIVE,
            $excludeEndingSoon ? $this->config->getEndingSoonDays() : 0
        );

        if (! $eventIds) {
            return [];
        }

        return $this->getItems($eventIds);
    }

    /**
     * @param array $eventIds
     * @return \Plumrocket\PrivateSale\Api\Data\EventInterface[]
     */
    private function getItems(array $eventIds): array
    {
        $this->searchCriteriaBuilder->addFilter(
            EventInterface::IDENTIFIER,
            $eventIds,
            'in'
        );

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchResult = $this->eventRepository->getList($searchCriteria);
        return $searchResult->getItems();
    }
}
