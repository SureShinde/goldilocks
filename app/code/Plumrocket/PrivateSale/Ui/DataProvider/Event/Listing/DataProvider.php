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
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Ui\DataProvider\Event\Listing;

use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Plumrocket\PrivateSale\Model\ResourceModel\Event\CollectionFactory;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var array
     */
    private $filterParams;

    /**
     * DataProvider constructor.
     *
     * @param string                                                              $name
     * @param string                                                              $primaryFieldName
     * @param string                                                              $requestFieldName
     * @param \Magento\Framework\App\RequestInterface                             $request
     * @param \Plumrocket\PrivateSale\Model\ResourceModel\Event\CollectionFactory $collectionFactory
     * @param array                                                               $meta
     * @param array                                                               $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        RequestInterface $request,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->request = $request;
        $this->filterParams = $this->prepareUpdateUrl();
    }

    /**
     * @inheritDoc
     */
    public function getData()
    {
        /** @var \Plumrocket\PrivateSale\Model\ResourceModel\Event\Collection $collection */
        $collection = $this->getCollection();
        if (! $collection->isLoaded()) {
            $collection->addAttributeToSelect('*');
            $collection->addStatusToCollection();
            $filter = [];

            if (isset($this->filterParams['category_event'])) {
                $filter[] = [
                    'attribute' => 'category_event',
                    'in' => explode('-', $this->filterParams['category_event'])
                ];
            }

            if (isset($this->filterParams['product_event'])) {
                $filter[] = [
                    'attribute' => 'product_event',
                    'in' => explode('-', $this->filterParams['product_event'])
                ];
            }

            if (! empty($filter)) {
                $collection->addFieldToFilter($filter);
            }

            if (isset($this->filterParams['status'])) {
                $statusRequest = explode('-', $this->filterParams['status']);
                $ids = [];

                $collectionFilter = clone $collection;
                foreach ($collectionFilter as $item) {
                    if (in_array($item['status'], $statusRequest, false)) {
                        $ids[] = $item->getEntityId();
                    }
                }

                $collection->addFieldToFilter('entity_id', ['in' => $ids]);
            }

            $collection->load();
        }

        $items = array_values($collection->toArray());

        return [
            'totalRecords' => $collection->getSize(),
            'items'        => $this->prepareItems($items),
        ];
    }

    /**
     * @return array
     */
    protected function prepareUpdateUrl()
    {
        $result = [];

        if (! isset($this->data['config']['filter_url_params'])) {
            return $result;
        }

        foreach ($this->data['config']['filter_url_params'] as $paramName => $paramValue) {
            if ('*' === $paramValue) {
                $paramValue = $this->request->getParam($paramName);
            }

            if ($paramValue) {
                $this->data['config']['update_url'] = sprintf(
                    '%s%s/%s/',
                    $this->data['config']['update_url'],
                    $paramName,
                    $paramValue
                );

                $result[$paramName] = $paramValue;
            }
        }

        return $result;
    }

    /**
     * @param \Plumrocket\PrivateSale\Api\Data\EventInterface[] $items
     * @return \Plumrocket\PrivateSale\Api\Data\EventInterface[]
     */
    protected function prepareItems(array $items): array
    {
        return $items;
    }
}
