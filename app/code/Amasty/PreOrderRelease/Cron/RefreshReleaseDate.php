<?php

declare(strict_types=1);

namespace Amasty\PreOrderRelease\Cron;

use Amasty\PreOrderRelease\Model\ConfigProvider;
use Amasty\PreOrderRelease\Model\ResourceModel\Product\LoadIdsWithExpiredReleaseDate;
use Amasty\PreOrderRelease\Model\ResourceModel\Product\UpdateBackordersValue;
use Amasty\PreOrderRelease\Model\Source\ChangeBackorders;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Model\Indexer\Stock\Processor;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Indexer\CacheContext;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class RefreshReleaseDate
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var LoadIdsWithExpiredReleaseDate
     */
    private $loadIdsWithExpiredReleaseDate;

    /**
     * @var UpdateBackordersValue
     */
    private $updateBackordersValue;

    /**
     * @var Processor
     */
    private $stockProcessor;

    /**
     * @var CacheContext
     */
    private $cacheContext;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    public function __construct(
        ConfigProvider $configProvider,
        TimezoneInterface $timezone,
        DateTime $dateTime,
        LoadIdsWithExpiredReleaseDate $loadIdsWithExpiredReleaseDate,
        UpdateBackordersValue $updateBackordersValue,
        Processor $stockProcessor,
        CacheContext $cacheContext,
        ManagerInterface $eventManager
    ) {
        $this->configProvider = $configProvider;
        $this->timezone = $timezone;
        $this->dateTime = $dateTime;
        $this->loadIdsWithExpiredReleaseDate = $loadIdsWithExpiredReleaseDate;
        $this->updateBackordersValue = $updateBackordersValue;
        $this->stockProcessor = $stockProcessor;
        $this->cacheContext = $cacheContext;
        $this->eventManager = $eventManager;
    }

    public function execute(): void
    {
        $currDate = $this->dateTime->formatDate($this->timezone->scopeDate(), false);
        $productIds = $this->loadIdsWithExpiredReleaseDate->execute($currDate);
        if ($this->configProvider->getNewBackordersValue() === ChangeBackorders::NO) {
            $this->cacheContext->registerEntities(Product::CACHE_TAG, $productIds);
            $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);
        } else {
            if (count($productIds)) {
                $this->updateBackordersValue->execute($productIds, $this->configProvider->getNewBackordersValue());
                $this->stockProcessor->reindexList($productIds);
            }
        }
    }
}
