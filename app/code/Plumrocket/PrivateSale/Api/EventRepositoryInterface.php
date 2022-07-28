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

namespace Plumrocket\PrivateSale\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Plumrocket\PrivateSale\Api\Data\EventInterface;
use Magento\Store\Model\Store;

interface EventRepositoryInterface
{
    /**
     * @param \Plumrocket\PrivateSale\Api\Data\EventInterface $event
     * @return \Plumrocket\PrivateSale\Api\Data\EventInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Plumrocket\PrivateSale\Api\Data\EventInterface $event): EventInterface;

    /**
     * Get event by id
     *
     * @param int $id
     * @param int $storeId
     * @return \Plumrocket\PrivateSale\Api\Data\EventInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id, $storeId = Store::DEFAULT_STORE_ID): EventInterface;

    /**
     * Delete event
     *
     * @param \Plumrocket\PrivateSale\Api\Data\EventInterface $event
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete(\Plumrocket\PrivateSale\Api\Data\EventInterface $event): bool;

    /**
     * @param int $id
     * @return bool Will returned true if deleted
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function deleteById($id): bool;

    /**
     * Retrieve events matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Plumrocket\PrivateSale\Api\Data\EventSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
