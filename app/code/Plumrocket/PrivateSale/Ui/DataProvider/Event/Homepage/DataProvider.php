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

namespace Plumrocket\PrivateSale\Ui\DataProvider\Event\Homepage;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Plumrocket\PrivateSale\Model\Event;
use Plumrocket\PrivateSale\Model\EventStatistics\IndexData;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var \Plumrocket\PrivateSale\Model\EventStatistics\IndexData
     */
    private $statisticsIndexData;

    /**
     * DataProvider constructor.
     *
     * @param string                                                  $name
     * @param string                                                  $primaryFieldName
     * @param string                                                  $requestFieldName
     * @param CollectionFactory                                       $collectionFactory
     * @param \Plumrocket\PrivateSale\Model\EventStatistics\IndexData $statisticsIndexData
     * @param array                                                   $meta
     * @param array                                                   $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        IndexData $statisticsIndexData,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );

        $this->statisticsIndexData = $statisticsIndexData;
        $this->collection = $collectionFactory->create()
            ->addAttributeToSelect('*');
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getData()
    {
        $items = $this->getCollection()
            ->setStoreId(0)
            ->addFieldToFilter('display_mode', ['eq' => Event::DM_HOMEPAGE])
            ->toArray();

        //Apply Statistics for Grid Items
        $items = $this->statisticsIndexData->applyToHomepageItems(array_values($items));

        return [
            'totalRecords' => $this->count(),
            'items' => $items
        ];
    }
}
