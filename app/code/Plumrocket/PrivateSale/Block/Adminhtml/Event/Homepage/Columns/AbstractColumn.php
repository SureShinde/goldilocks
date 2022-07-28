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

namespace Plumrocket\PrivateSale\Block\Adminhtml\Event\Homepage\Columns;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Plumrocket\PrivateSale\Model\ResourceModel\Event\Collection as EventCollection;
use Plumrocket\PrivateSale\Model\ResourceModel\Event\CollectionFactory;

abstract class AbstractColumn extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $viewUrlPath;

    /**
     * @var array
     */
    protected $urlParams;

    /**
     * @var CollectionFactory
     */
    protected $collection;

    /**
     * @var TimezoneInterface
     */
    protected $date;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var array[]
     */
    private $allCategoriesForHomepage = [];

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var array[]
     */
    private $singleEventIds;

    /**
     * AbstractColumn constructor.
     * @param ProductRepositoryInterface $productRepository
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param CollectionFactory $collectionFactory
     * @param UrlInterface $urlBuilder
     * @param TimezoneInterface $date
     * @param CategoryRepositoryInterface $categoryRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CollectionFactory $collectionFactory,
        UrlInterface $urlBuilder,
        TimezoneInterface $date,
        CategoryRepositoryInterface $categoryRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );

        $this->urlBuilder = $urlBuilder;
        $this->collection = $collectionFactory;
        $this->date = $date;
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = [
                    'view' => [
                        'href'  => $this->getHref((int) $item['entity_id']),
                        'label' => $this->label,
                    ]
                ];
            }
        }

        return $dataSource;
    }

    /**
     * @param $categoryId
     * @return string
     */
    private function getHref($categoryId)
    {
        $this->initEventsData($categoryId);
        $categoryIds = $this->mergeAllChildrenCategoryIds($categoryId, true);
        $productIds = $this->getAllSingleProductIds($categoryIds);
        $params['category_event'] = $this->mergeAllChildrenCategoryIds($categoryId);

        if (! empty($productIds)) {
            $params['product_event'] = $productIds;
        }

        $this->urlParams = array_merge($params, $this->urlParams);

        return $this->label === '0' ? '#' : $this->urlBuilder->getUrl($this->viewUrlPath, $this->urlParams);
    }

    /**
     * Set label, params
     *
     * @param int $categoryId
     * @return $this
     */
    abstract protected function initEventsData(int $categoryId): AbstractColumn;

    /**
     * @param $value
     * @return $this
     */
    public function setViewUrlPath($value)
    {
        $this->viewUrlPath = $value;

        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setUrlParams($value)
    {
        $this->urlParams = $value;

        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setLabel($value)
    {
        $this->label = $value;

        return $this;
    }

    /**
     * @param int $categoryId
     * @return EventCollection
     * @throws NoSuchEntityException
     */
    protected function getEventsForCategory(int $categoryId): EventCollection
    {
        $allCategoriesForHomepage = $this->mergeAllChildrenCategoryIds($categoryId, true);
        $allProductsForHomepage = $this->getAllSingleProductIds($allCategoriesForHomepage, true);

        /** @var \Plumrocket\PrivateSale\Model\ResourceModel\Event\Collection $eventCollection */
        $eventCollection = $this->collection->create();
        $eventCollection->addFieldToFilter(
            [
                ['attribute' => 'category_event', 'in' => $allCategoriesForHomepage],
                ['attribute' => 'product_event', 'in' => $allProductsForHomepage]
            ]
        );

        return $eventCollection;
    }

    /**
     * @param int  $categoryId
     * @param bool $asArray
     * @return int[]|string
     */
    private function mergeAllChildrenCategoryIds(int $categoryId, $asArray = false)
    {
        if (! isset($this->allCategoriesForHomepage[$categoryId])) {
            try {
                $category = $this->categoryRepository->get($categoryId);
                $this->allCategoriesForHomepage[$categoryId] = array_merge(
                    [$categoryId],
                    $category->getAllChildren(true)
                );
            } catch (NoSuchEntityException $e) {
                $this->allCategoriesForHomepage[$categoryId] = [$categoryId];
            }
        }

        return $asArray
            ? $this->allCategoriesForHomepage[$categoryId]
            : implode('-', $this->allCategoriesForHomepage[$categoryId]);
    }

    /**
     * @param array $categoryIds
     * @param bool $asArray
     * @return array|string
     */
    private function getAllSingleProductIds(array $categoryIds, $asArray = false)
    {
        if (empty($categoryIds)) {
            return $asArray ? [] : '';
        }

        $categoryIdsKey = implode('_', $categoryIds);

        if (! isset($this->singleEventIds[$categoryIdsKey])) {
            $this->singleEventIds[$categoryIdsKey] = [];
            $eventCollection = $this->collection->create()
                ->addFieldToFilter('product_event', ['neq' => null]);

            try {
                foreach ($eventCollection->toArray(['product_event']) as $key => $item) {
                    $product = $this->productRepository->getById($item['product_event']);
                    if ($product && ($productCategoryIds = $product->getCategoryIds())
                        && array_intersect($categoryIds, $productCategoryIds)
                    ) {
                        $this->singleEventIds[$categoryIdsKey][] = $item['product_event'];
                    }
                }
            } catch (NoSuchEntityException $e) {
            }
        }

        return $asArray
            ? $this->singleEventIds[$categoryIdsKey]
            : implode('-', $this->singleEventIds[$categoryIdsKey]);
    }
}
