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

namespace Plumrocket\PrivateSale\Model\Catalog\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\WebsiteRepositoryInterface;

/**
 * Retrieve array of product id with children product ids (if product has children)
 *
 * @since v5.0.0
 */
class GetUsedProductIdsById
{
    /**
     * @var \Plumrocket\PrivateSale\Model\Catalog\Product\GetUsedProductIds
     */
    private $getUsedProductIds;

    /**
     * @var \Magento\Store\Api\WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * GetUsedProducts constructor.
     *
     * @param \Plumrocket\PrivateSale\Model\Catalog\Product\GetUsedProductIds $getUsedProductIds
     * @param \Magento\Store\Api\WebsiteRepositoryInterface                   $websiteRepository
     * @param \Magento\Catalog\Api\ProductRepositoryInterface                 $productRepository
     */
    public function __construct(
        GetUsedProductIds $getUsedProductIds,
        WebsiteRepositoryInterface $websiteRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->getUsedProductIds = $getUsedProductIds;
        $this->websiteRepository = $websiteRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * @param int      $productId
     * @param int|null $storeId
     * @param int|null $websiteId
     * @return array
     * @throws \Magento\Framework\Exception\InputException
     */
    public function execute(int $productId, int $storeId = null, int $websiteId = null): array
    {
        if (null === $storeId) {
            $storeId = $this->websiteRepository->getById($websiteId)->getDefaultGroup()->getDefaultStoreId();
        }

        try {
            $product = $this->productRepository->getById($productId, false, $storeId);
            return $this->getUsedProductIds->execute($product);
        } catch (NoSuchEntityException $e) {
            return [];
        }
    }
}
