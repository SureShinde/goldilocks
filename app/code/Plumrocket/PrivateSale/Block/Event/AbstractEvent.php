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
 * @package     Plumrocket Private Sales and Flash Sales v4.x.x
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Block\Event;

use Magento\Framework\View\Element\Template;
use Plumrocket\PrivateSale\Helper\EventHeaderStyle;
use Plumrocket\PrivateSale\Helper\Timer as TimerHelper;

abstract class AbstractEvent extends Template
{
    /**
     * Helper
     * @var \Plumrocket\PrivateSale\Helper\Data
     */
    protected $helper;

    /**
     * Registry
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Plumrocket\PrivateSale\Model\CurrentDateTime
     */
    protected $currentDateTime;

    /**
     * @var \Plumrocket\PrivateSale\Model\Event\Catalog
     */
    protected $eventCatalog;

    /**
     * @var \Plumrocket\PrivateSale\Helper\Timer
     */
    protected $timerHelper;

    /**
     * @var EventHeaderStyle
     */
    protected $eventHeaderStyle;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * AbstractEvent constructor.
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param EventHeaderStyle $eventHeaderStyle
     * @param \Magento\Framework\Registry $registry
     * @param \Plumrocket\PrivateSale\Helper\Data $dataHelper
     * @param TimerHelper $timerHelper
     * @param Template\Context $context
     * @param \Plumrocket\PrivateSale\Model\CurrentDateTime $currentDateTime
     * @param \Plumrocket\PrivateSale\Model\Event\Catalog $eventCatalog
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        EventHeaderStyle $eventHeaderStyle,
        \Magento\Framework\Registry $registry,
        \Plumrocket\PrivateSale\Helper\Data $dataHelper,
        TimerHelper $timerHelper,
        \Magento\Framework\View\Element\Template\Context $context,
        \Plumrocket\PrivateSale\Model\CurrentDateTime $currentDateTime,
        \Plumrocket\PrivateSale\Model\Event\Catalog $eventCatalog,
        $data = []
    ) {
        $this->helper = $dataHelper;
        $this->registry = $registry;
        $this->currentDateTime = $currentDateTime;
        $this->eventCatalog = $eventCatalog;
        $this->timerHelper = $timerHelper;
        $this->eventHeaderStyle = $eventHeaderStyle;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;

        parent::__construct($context, $data);
    }

    /**
     * Retrieve Event or null
     *
     * @return \Plumrocket\PrivateSale\Api\Data\EventInterface|\Plumrocket\PrivateSale\Model\Event|null
     */
    abstract public function getEvent();

    /**
     * @return string
     */
    abstract public function getTimerLayout(): string;

    /**
     * Retrieve event id or 0
     *
     * @return int
     */
    public function getEventId(): int
    {
        return $this->getEvent() ? $this->getEvent()->getId() : 0;
    }

    /**
     * Display countdown
     * This option currently retrieve only true, but in future it can be changed and added logic
     * @return boolean
     */
    public function displayCountdown(): bool
    {
        return $this->getEvent() &&
            ! empty($this->getTimerLayout()) &&
            $this->getTimerLayout() !== TimerHelper::DISABLED;
    }

    /**
     * @param $eventDate
     * @return string
     */
    public function formatStaticDate($eventDate)
    {
        $currentDate = $this->currentDateTime->convertToCurrentTimezone($eventDate);

        return $currentDate->format(TimerHelper::STATIC_DATE_FORMAT);
    }
}
