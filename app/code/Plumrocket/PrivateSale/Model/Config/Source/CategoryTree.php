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

namespace Plumrocket\PrivateSale\Model\Config\Source;

use Plumrocket\PrivateSale\Model\Event;
use Magento\Framework\App\RequestInterface;
use Plumrocket\PrivateSale\Api\Data\EventInterface;
use Magento\Catalog\Ui\Component\Product\Form\Categories\Options;
use Plumrocket\PrivateSale\Model\ResourceModel\Event\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

class CategoryTree extends Options
{
    /**
     * @var CollectionFactory
     */
    protected $eventCollectionFactory;

    /**
     * @var array
     */
    private $eventPageCategoriesIds;

    /**
     * @var array
     */
    private $categoriesIdsWithEvents;

    /**
     * @var array
     */
    private $categoriesIdsWithPrivateEvents;

    /**
     * CategoryTree constructor.
     *
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param RequestInterface $request
     * @param CollectionFactory $eventCollectionFactory
     */
    public function __construct(
        CategoryCollectionFactory $categoryCollectionFactory,
        RequestInterface $request,
        CollectionFactory $eventCollectionFactory
    ) {
        parent::__construct($categoryCollectionFactory, $request);
        $this->eventCollectionFactory = $eventCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->appyEventDataToCategoryData(parent::toOptionArray());
    }

    /**
     * @param array $categories
     */
    private function appyEventDataToCategoryData(array $categories)
    {
        $categoryesIdsIsEventPage = $this->getEventPageCategoriesIds();
        $categoriesIdsWithEvents = $this->getCategoriesIdsWithEvents();
        $categoriesIdsWithPrivateEvents = $this->getCategoriesIdsWithPrivateEvents();

        foreach ($categories as & $categoryData) {
            if (in_array($categoryData['value'], $categoryesIdsIsEventPage)) {
                $categoryData['eventHomepage'] = 1;
            }

            if (in_array($categoryData['value'], $categoriesIdsWithEvents)) {
                $categoryData['event'] = 1;
            }

            if (in_array($categoryData['value'], $categoriesIdsWithPrivateEvents)) {
                $categoryData['privateEvent'] = 1;
            }

            if (isset($categoryData['optgroup'])) {
                $categoryData['optgroup'] = $this->appyEventDataToCategoryData($categoryData['optgroup']);
            }
        }

        return $categories;
    }

    /**
     * @return array
     */
    private function getEventPageCategoriesIds()
    {
        if (null === $this->eventPageCategoriesIds) {
            $this->eventPageCategoriesIds = $this->categoryCollectionFactory->create()
                ->addFieldToFilter('display_mode', Event::DM_HOMEPAGE)
                ->getAllIds();
        }

        return $this->eventPageCategoriesIds;
    }

    /**
     * @return array
     */
    private function getCategoriesIdsWithEvents()
    {
        if (null === $this->categoriesIdsWithEvents) {
            $this->categoriesIdsWithEvents = $this->eventCollectionFactory->create()
                ->addAttributeToFilter(EventInterface::CATEGORY_EVENT, ['notnull' => 'value'], 'left')
                ->getColumnValues(EventInterface::CATEGORY_EVENT);
        }

        return $this->categoriesIdsWithEvents;
    }

    /**
     * @return array
     */
    private function getCategoriesIdsWithPrivateEvents()
    {
        if (null === $this->categoriesIdsWithPrivateEvents) {
            $this->categoriesIdsWithPrivateEvents = $this->eventCollectionFactory->create()
                ->addAttributeToFilter(EventInterface::CATEGORY_EVENT, ['notnull' => 'value'], 'left')
                ->addFieldToFilter('is_event_private', true)
                ->getColumnValues(EventInterface::CATEGORY_EVENT);
        }

        return $this->categoriesIdsWithPrivateEvents;
    }
}
