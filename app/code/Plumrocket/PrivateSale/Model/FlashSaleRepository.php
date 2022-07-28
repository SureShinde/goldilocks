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
use Plumrocket\PrivateSale\Api\Data\FlashSaleInterface;
use Plumrocket\PrivateSale\Api\FlashSaleRepositoryInterface;

class FlashSaleRepository implements FlashSaleRepositoryInterface
{
    /**
     * @var \Plumrocket\PrivateSale\Api\Data\FlashSaleInterfaceFactory
     */
    private $saleFactory;

    /**
     * @var ResourceModel\FlashSale
     */
    private $resourceModel;

    /**
     * @var ResourceModel\FlashSale\CollectionFactory
     */
    private $saleCollectionFactory;

    /**
     * @var \Magento\Framework\Api\SearchResultsFactory
     */
    private $searchResultsFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * FlashSaleRepository constructor.
     *
     * @param \Plumrocket\PrivateSale\Api\Data\FlashSaleInterfaceFactory $saleFactory
     * @param ResourceModel\FlashSale $resourceModel
     * @param ResourceModel\FlashSale\CollectionFactory $saleCollectionFactory
     * @param \Magento\Framework\Api\SearchResultsFactory $searchResultsFactory
     * @param \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        \Plumrocket\PrivateSale\Api\Data\FlashSaleInterfaceFactory $saleFactory,
        \Plumrocket\PrivateSale\Model\ResourceModel\FlashSale $resourceModel,
        \Plumrocket\PrivateSale\Model\ResourceModel\FlashSale\CollectionFactory $saleCollectionFactory,
        \Magento\Framework\Api\SearchResultsFactory $searchResultsFactory,
        \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor
    ) {
        $this->saleFactory = $saleFactory;
        $this->resourceModel = $resourceModel;
        $this->saleCollectionFactory = $saleCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(FlashSaleInterface $sale): FlashSaleInterface
    {
        try {
            $this->resourceModel->save($sale);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $sale;
    }

    /**
     * @inheritDoc
     */
    public function getById($id): FlashSaleInterface
    {
        $sale = $this->saleFactory->create();
        $this->resourceModel->load($sale, $id);

        if (! $sale->getId()) {
            throw new NoSuchEntityException(__('The sale with the "%1" ID doesn\'t exist.', $id));
        }

        return $sale;
    }

    /**
     * @inheritDoc
     */
    public function delete(FlashSaleInterface $sale): bool
    {
        try {
            $this->resourceModel->delete($sale);
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
        /** @var ResourceModel\FlashSale\Collection $collection */
        $collection = $this->saleCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }
}
