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

namespace Plumrocket\PrivateSale\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Plumrocket\PrivateSale\Api\Data\EventStatisticsInterface;
use Plumrocket\PrivateSale\Api\EventStatisticsRepositoryInterface;

class EventStatisticsRepository implements EventStatisticsRepositoryInterface
{
    /**
     * @var \Plumrocket\PrivateSale\Api\Data\EventStatisticsInterfaceFactory
     */
    private $eventStatisticsFactory;

    /**
     * @var ResourceModel\EventStatistics
     */
    private $resourceModel;

    /**
     * @var ResourceModel\EventStatistics\CollectionFactory
     */
    private $eventStatisticsCollectionFactory;

    /**
     * @var \Magento\Framework\Api\SearchResultsFactory
     */
    private $searchResultsFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * EventStatisticsRepository constructor.
     * @param \Plumrocket\PrivateSale\Api\Data\EventStatisticsInterfaceFactory $eventStatisticsFactory
     * @param ResourceModel\EventStatistics $resourceModel
     * @param ResourceModel\EventStatistics\CollectionFactory $eventStatisticsCollectionFactory
     * @param \Magento\Framework\Api\SearchResultsFactory $searchResultsFactory
     * @param \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        \Plumrocket\PrivateSale\Api\Data\EventStatisticsInterfaceFactory $eventStatisticsFactory,
        \Plumrocket\PrivateSale\Model\ResourceModel\EventStatistics $resourceModel,
        \Plumrocket\PrivateSale\Model\ResourceModel\EventStatistics\CollectionFactory $eventStatisticsCollectionFactory,
        \Magento\Framework\Api\SearchResultsFactory $searchResultsFactory,
        \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor
    ) {
        $this->eventStatisticsFactory = $eventStatisticsFactory;
        $this->resourceModel = $resourceModel;
        $this->eventStatisticsCollectionFactory = $eventStatisticsCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(EventStatisticsInterface $eventStatistics): EventStatisticsInterface
    {
        try {
            $this->resourceModel->save($eventStatistics);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $eventStatistics;
    }

    /**
     * @inheritDoc
     */
    public function getById($id, $field = null): EventStatisticsInterface
    {
        $eventStatistics = $this->eventStatisticsFactory->create();
        $this->resourceModel->load($eventStatistics, $id, $field);

        if (! $eventStatistics->getId()) {
            throw new NoSuchEntityException(__('The event statistics with the "%1" ID doesn\'t exist.', $id));
        }

        return $eventStatistics;
    }

    /**
     * @inheritDoc
     */
    public function delete(EventStatisticsInterface $eventStatistics): bool
    {
        try {
            $this->resourceModel->delete($eventStatistics);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($id): bool
    {
        return $this->delete($this->getById($id));
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var ResourceModel\EventStatistics\Collection $collection */
        $collection = $this->eventStatisticsCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }
}
