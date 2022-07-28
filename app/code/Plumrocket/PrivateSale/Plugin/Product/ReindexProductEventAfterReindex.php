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

namespace Plumrocket\PrivateSale\Plugin\Product;

use Magento\Catalog\Model\Product;
use Plumrocket\PrivateSale\Helper\Config;

/**
 * @since 5.0.0
 */
class ReindexProductEventAfterReindex
{
    /**
     * Used for disabling plugin while move product in categories
     *
     * @var bool
     */
    private $status = true;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    private $catalogProduct;

    /**
     * @var \Plumrocket\PrivateSale\Model\Indexer\Product
     */
    private $productEventIndexer;

    /**
     * @var \Plumrocket\PrivateSale\Helper\Config
     */
    private $config;

    /**
     * HideAddToCart constructor.
     *
     * @param \Magento\Catalog\Helper\Product               $catalogProduct
     * @param \Plumrocket\PrivateSale\Model\Indexer\Product $productEventIndexer
     * @param \Plumrocket\PrivateSale\Helper\Config         $config
     */
    public function __construct(
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Plumrocket\PrivateSale\Model\Indexer\Product $productEventIndexer,
        Config $config
    ) {
        $this->catalogProduct = $catalogProduct;
        $this->productEventIndexer = $productEventIndexer;
        $this->config = $config;
    }

    /**
     * @param \Magento\Catalog\Model\Product $subject
     */
    public function afterReindex(Product $subject)
    {
        if (! $this->isActive() || ! $this->config->isModuleEnabled()) {
            return;
        }

        // reset array keys in order to fix false positive check
        $websiteIds = $subject->getData('website_ids');
        if (is_array($websiteIds)) {
            $subject->setData('website_ids', array_values($websiteIds));
        }

        if ($this->catalogProduct->isDataForProductCategoryIndexerWasChanged($subject) || $subject->isDeleted()) {
            $this->productEventIndexer->executeRow($subject->getId());
        }

        $subject->setData('website_ids', $websiteIds);
    }

    public function setActive(bool $flag): ReindexProductEventAfterReindex
    {
        $this->status = $flag;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->status;
    }
}
