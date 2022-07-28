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
use Plumrocket\PrivateSale\Api\Data\FlashSaleInterface;

interface FlashSaleRepositoryInterface
{
    /**
     * @param \Plumrocket\PrivateSale\Api\Data\FlashSaleInterface $sale
     * @return \Plumrocket\PrivateSale\Api\Data\FlashSaleInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(
        \Plumrocket\PrivateSale\Api\Data\FlashSaleInterface $sale
    ): FlashSaleInterface;

    /**
     * Get Flash Sale by id
     *
     * @param int $id
     * @return \Plumrocket\PrivateSale\Api\Data\FlashSaleInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id): FlashSaleInterface;

    /**
     * Delete Flash Sale
     *
     * @param \Plumrocket\PrivateSale\Api\Data\FlashSaleInterface $sale
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete(\Plumrocket\PrivateSale\Api\Data\FlashSaleInterface $sale): bool;

    /**
     * @param int $id
     * @return bool Will returned true if deleted
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function deleteById($id): bool;

    /**
     * Retrieve Flash Sales matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
