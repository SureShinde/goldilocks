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

namespace Plumrocket\PrivateSale\Ui\Component\Listing;

use Magento\Catalog\Api\CategoryListInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class Columns extends \Magento\Ui\Component\Listing\Columns
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CategoryListInterface
     */
    private $categoryList;

    /**
     * Columns constructor.
     *
     * @param ContextInterface $context
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CategoryListInterface $categoryList
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CategoryListInterface $categoryList,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->categoryList = $categoryList;
    }

    /**
     * @inheritDoc
     */
    public function prepare()
    {
        $categoryIds = array_column($this->getContext()->getDataProvider()->getData()['items'], 'category_event');
        $categoryIds = array_unique($categoryIds);
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('entity_id', $categoryIds, 'in')
            ->create();

        $categories = $this->categoryList->getList($searchCriteria)->getItems();
        $categories = $this->refactorItemsKey($categories);
        $configData = $this->getContext()->getDataProvider()->getConfigData();
        $configData['categories'] = $categories;
        $this->getContext()->getDataProvider()->setConfigData($configData);

        return parent::prepare();
    }

    /**
     * @param array $items
     * @return array
     */
    private function refactorItemsKey(array $items): array
    {
        $newItems = [];

        foreach ($items as $item) {
            $newItems[$item->getId()] = $item;
        }

        return $newItems;
    }
}
