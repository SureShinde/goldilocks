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
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Store\Model\Store;
use Plumrocket\PrivateSale\Api\Data\EventInterface;
use Plumrocket\PrivateSale\Api\EventRepositoryInterface;

class EventRepository implements EventRepositoryInterface
{
    /**
     * @var \Plumrocket\PrivateSale\Api\Data\EventInterfaceFactory
     */
    protected $eventFactory;

    /**
     * @var ResourceModel\Event
     */
    protected $resourceModel;

    /**
     * @var ResourceModel\Event\CollectionFactory
     */
    protected $eventCollectionFactory;

    /**
     * @var \Plumrocket\PrivateSale\Api\Data\EventSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var array
     */
    private $instancesById = [];

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * EventRepository constructor.
     *
     * @param \Plumrocket\PrivateSale\Api\Data\EventInterfaceFactory $eventFactory
     * @param ResourceModel\Event $resourceModel
     * @param ResourceModel\Event\CollectionFactory $eventCollectionFactory
     * @param \Plumrocket\PrivateSale\Api\Data\EventSearchResultsInterfaceFactory $searchResultsFactory
     * @param \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Plumrocket\PrivateSale\Api\Data\EventInterfaceFactory $eventFactory,
        \Plumrocket\PrivateSale\Model\ResourceModel\Event $resourceModel,
        \Plumrocket\PrivateSale\Model\ResourceModel\Event\CollectionFactory $eventCollectionFactory,
        \Plumrocket\PrivateSale\Api\Data\EventSearchResultsInterfaceFactory $searchResultsFactory,
        \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->eventFactory = $eventFactory;
        $this->resourceModel = $resourceModel;
        $this->eventCollectionFactory = $eventCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritDoc
     */
    public function save(EventInterface $event): EventInterface
    {
        try {
            $this->resourceModel->save($event);
            $this->instancesById[$event->getId()][(int) $this->storeManager->getStore()->getId()] = $event;
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $event;
    }

    /**
     * @inheritDoc
     */
    public function getById($id, $storeId = null): EventInterface
    {
        if (null === $storeId) {
            $storeId = (int) $this->storeManager->getStore()->getId();
        }

        if (! isset($this->instancesById[$id][$storeId])) {
            $event = $this->eventFactory->create()->setStoreId($storeId);
            $this->resourceModel->load($event, $id);

            if (! $event->getId()) {
                throw new NoSuchEntityException(__('The event with the "%1" ID doesn\'t exist.', $id));
            }

            $this->instancesById[$id][$storeId] = $event;
        }

        return $this->instancesById[$id][$storeId];
    }

    /**
     * @inheritDoc
     */
    public function delete(EventInterface $events): bool
    {
        try {
            $this->resourceModel->delete($events);
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
        /** @var ResourceModel\Event\Collection $collection */
        $collection = $this->eventCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $collection->addAttributeToSelect('*')->addStatusToCollection();
        /** @var \Plumrocket\PrivateSale\Api\Data\EventSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }
}
