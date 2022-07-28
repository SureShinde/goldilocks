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
 * @package     Plumrocket_magento2.3.4
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Block\Event\Widget;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManager;
use Plumrocket\PrivateSale\Helper\Config;
use Plumrocket\PrivateSale\Helper\Preview;
use Plumrocket\PrivateSale\Model\Catalog\Category\GetChildrenIds;
use Plumrocket\PrivateSale\ViewModel\Event;

class EndingSoon extends AbstractWidget
{
    /**
     * @var \Plumrocket\PrivateSale\Model\Catalog\Category\GetChildrenIds
     */
    private $getCategoryChildrenIds;

    /**
     * @var \Plumrocket\PrivateSale\Helper\Preview
     */
    private $preview;

    /**
     * ComingSoon constructor.
     *
     * @param \Magento\Framework\Registry                           $registry
     * @param \Magento\Store\Model\StoreManager                     $storeManager
     * @param \Plumrocket\PrivateSale\Helper\Config                 $config
     * @param \Magento\Framework\View\Element\Template\Context      $context
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface      $categoryRepository
     * @param \Plumrocket\PrivateSale\ViewModel\Event               $eventViewModel
     * @param \Plumrocket\PrivateSale\Model\Catalog\Category\GetChildrenIds $getCategoryChildrenIds
     * @param \Plumrocket\PrivateSale\Helper\Preview                $preview
     * @param array                                                 $data
     */
    public function __construct(
        Registry $registry,
        StoreManager $storeManager,
        Config $config,
        Context $context,
        CategoryRepositoryInterface $categoryRepository,
        Event $eventViewModel,
        GetChildrenIds $getCategoryChildrenIds,
        Preview $preview,
        array $data = []
    ) {
        parent::__construct(
            $registry,
            $storeManager,
            $config,
            $context,
            $categoryRepository,
            $eventViewModel,
            $data
        );
        $this->getCategoryChildrenIds = $getCategoryChildrenIds;
        $this->preview = $preview;
    }

    /**
     * @inheritDoc
     */
    public function getEvents(): array
    {
        $category = $this->getCategory();

        if (! $category) {
            return [];
        }

        if ($this->events === null) {
            $this->events = [];
            if ($this->preview->isAllowToChangeData()) {
                $allChildIds = $this->getCategoryChildrenIds->execute($category, true, true);
            } else {
                $allChildIds = $category->getAllChildren(true);
            }

            if ($allChildIds) {
                $events = $this->eventViewModel->getEndingSoonEvents($this->getEndingSoonDays(), $allChildIds);

                if (! empty($events)) {
                    $this->setEventsExist();
                    $this->events = $this->sortEventsByEndTime($events);
                }
            }
        }

        return $this->events;
    }
}
