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

namespace Plumrocket\PrivateSale\Ui\DataProvider\Discount\Listing;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\UiComponent\DataProvider\DocumentFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Plumrocket\PrivateSale\Model\Catalog\Product\GetUsedProductIds;
use Plumrocket\PrivateSale\Model\Config\Source\EventType;
use Plumrocket\PrivateSale\Model\ResourceModel\Discount\Grid\CollectionFactory;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var DocumentFactory
     */
    protected $documentFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Plumrocket\PrivateSale\Model\Catalog\Product\GetUsedProductIds
     */
    private $getUsedProductIds;

    /**
     * DataProvider constructor.
     *
     * @param string                                                                      $name
     * @param string                                                                      $primaryFieldName
     * @param string                                                                      $requestFieldName
     * @param \Plumrocket\PrivateSale\Model\ResourceModel\Discount\Grid\CollectionFactory $collectionFactory
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\DocumentFactory    $documentFactory
     * @param \Magento\Framework\App\RequestInterface                                     $request
     * @param \Magento\Catalog\Api\ProductRepositoryInterface                             $productRepository
     * @param \Plumrocket\PrivateSale\Model\Catalog\Product\GetUsedProductIds                     $getUsedProductIds
     * @param array                                                                       $meta
     * @param array                                                                       $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        DocumentFactory $documentFactory,
        RequestInterface $request,
        ProductRepositoryInterface $productRepository,
        GetUsedProductIds $getUsedProductIds,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->documentFactory = $documentFactory;
        $this->request = $request;
        $this->productRepository = $productRepository;
        $this->getUsedProductIds = $getUsedProductIds;
    }

    /**
     * @inheritDoc
     */
    public function getData()
    {
        $collection = $this->getCollection();
        $collection->addCategoryIds();

        return [
            'totalRecords' => $collection->getSize(),
            'items' => array_values($collection->toArray()),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getSearchCriteria()
    {
        return $this->getCollection();
    }

    /**
     * @inheritdoc
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        $this->getCollection()->addFieldToFilter(
            $filter->getField(),
            [$filter->getConditionType() => $filter->getValue()]
        );
    }

    /**
     * @inheritDoc
     */
    public function getSearchResult()
    {
        $collection = $this->getCollection();
        $this->cleanCollection($collection);
        $items = [];

        foreach ($collection->getItems() as $item) {
            $items[$item->getId()] = $this->documentFactory->create()->setData($item->getData());
        }

        $collection->setItems($items);

        return parent::getSearchResult();
    }

    /**
     * @inheritDoc
     */
    public function setLimit($offset, $size)
    {
        parent::setLimit($offset, $size);
        $this->cleanCollection($this->getCollection());
    }

    /**
     * @inheritDoc
     */
    public function getCollection()
    {
        /** @var \Plumrocket\PrivateSale\Model\ResourceModel\Discount\Grid\Collection $collection */
        $collection = parent::getCollection();

        if ('prprivatesale_discount_listing' === $this->request->getParam('namespace')) {
            $eventType = (int) $this->request->getParam('event_type');

            if (EventType::PRODUCT === $eventType) {
                $productId = $this->request->getParam('product_id');
                try {
                    $product = $this->productRepository->getById($productId);
                    $usedProductsIds = $this->getUsedProductIds->execute($product);

                    $collection->addFieldToFilter('entity_id', ['in' => $usedProductsIds]);
                } catch (NoSuchEntityException $e) {
                    $collection->addFieldToFilter('entity_id', $productId);
                }
            } elseif (EventType::CATEGORY === $eventType) {
                $categoryId = $this->request->getParam('category_id');
                $collection->addCategoriesFilter(['eq' => [$categoryId]]);
            }
        }

        return $collection;
    }

    /**
     * @param $collection
     */
    private function cleanCollection($collection)
    {
        $collection->setIsLoaded(false);
        $collection->removeAllItems();
    }
}
