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
 * @package     Plumrocket PrivateSale
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Ui\DataProvider\Event\Category\Product;

use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Plumrocket\PrivateSale\Model\Event\Homepage;
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
     * @var Homepage
     */
    private $homepage;

    /**
     * DataProvider constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param Homepage $homepage
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        Homepage $homepage,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->collection = $collectionFactory->create();
        $this->request = $request;
        $this->homepage = $homepage;
    }

    /**
     * @inheritDoc
     */
    public function getData()
    {
        /** @var \Plumrocket\PrivateSale\Model\ResourceModel\Event\Collection $collection */
        $collection = $this->getCollection();

        if (! $collection->isLoaded()) {
            $collection->addAttributeToSelect('*')->addStatusToCollection();

            if ($categoryId = $this->request->getParam('current_category_id')) {
                $collection->addFieldToFilter('category_event', $categoryId);
            } elseif ($productId = $this->request->getParam('current_product_id')) {
                $collection->addFieldToFilter('product_event', $productId);
            }

            $collection->load();
        }

        $items = array_values($collection->toArray());

        foreach ($items as & $item) {
            $item['event_homepage'] = $this->homepage->getNamesByEventId((int) $item['entity_id']);
        }

        return [
            'totalRecords' => $collection->getSize(),
            'items' => $items,
        ];
    }
}
