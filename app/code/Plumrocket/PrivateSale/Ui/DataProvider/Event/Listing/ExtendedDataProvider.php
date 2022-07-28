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
use Plumrocket\PrivateSale\Model\Event\Homepage;
use Plumrocket\PrivateSale\Model\EventStatistics\IndexData;
use Plumrocket\PrivateSale\Model\ResourceModel\Event\CollectionFactory;

class ExtendedDataProvider extends DataProvider
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Plumrocket\PrivateSale\Model\EventStatistics\IndexData
     */
    private $statisticsIndexData;

    /**
     * @var Homepage
     */
    private $eventHomepage;

    /**
     * @param string                                                              $name
     * @param string                                                              $primaryFieldName
     * @param string                                                              $requestFieldName
     * @param \Magento\Framework\App\RequestInterface                             $request
     * @param \Plumrocket\PrivateSale\Model\ResourceModel\Event\CollectionFactory $collectionFactory
     * @param \Plumrocket\PrivateSale\Model\EventStatistics\IndexData             $statisticsIndexData
     * @param \Plumrocket\PrivateSale\Model\Event\Homepage                        $homepage
     * @param array                                                               $meta
     * @param array                                                               $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        RequestInterface $request,
        CollectionFactory $collectionFactory,
        IndexData $statisticsIndexData,
        Homepage $homepage,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $request, $collectionFactory, $meta, $data);

        $this->statisticsIndexData = $statisticsIndexData;
        $this->collection = $collectionFactory->create();
        $this->eventHomepage = $homepage;
    }

    /**
     * @param array $items
     * @return array
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function prepareItems(array $items): array
    {
        //Apply Statistics for Grid Items
        $items = $this->statisticsIndexData->applyToEventItems($items);

        $preparedItems = [];
        foreach ($items as $item) {
            $item['event_homepage'] = $this->eventHomepage->getNamesByEventId((int) $item['entity_id']);
            $preparedItems[] = $item;
        }

        return $preparedItems;
    }
}
