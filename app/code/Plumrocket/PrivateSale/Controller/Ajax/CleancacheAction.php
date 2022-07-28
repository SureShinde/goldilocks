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

namespace Plumrocket\PrivateSale\Controller\Ajax;

use Magento\Config\Model\ResourceModel\Config as ConfigResource;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Plumrocket\PrivateSale\Helper\Config;
use Plumrocket\PrivateSale\Model\CleanCache;
use Plumrocket\PrivateSale\Model\Event\ProductEventIndexer;
use Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\Indexer;
use Plumrocket\PrivateSale\Model\Indexer\Product;
use Plumrocket\PrivateSale\Model\PriceCalculation;

class CleancacheAction extends \Magento\Framework\App\Action\Action
{
    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var PriceCalculation
     */
    protected $priceCalculation;

    /**
     * @var ReinitableConfigInterface
     */
    protected $appConfig;

    /**
     * @var ConfigResource
     */
    protected $configResource;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var CleanCache
     */
    private $cacheClean;

    /**
     * CleancacheAction constructor.
     * @param Context $context
     * @param IndexerRegistry $indexerRegistry
     * @param PriceCalculation $priceCalculation
     * @param ReinitableConfigInterface $appConfig
     * @param ConfigResource $configResource
     * @param DateTime $dateTime
     * @param CleanCache $cacheClean
     */
    public function __construct(
        Context $context,
        IndexerRegistry $indexerRegistry,
        PriceCalculation $priceCalculation,
        ReinitableConfigInterface $appConfig,
        ConfigResource $configResource,
        DateTime $dateTime,
        CleanCache $cacheClean
    ) {
        parent::__construct($context);

        $this->indexerRegistry = $indexerRegistry;
        $this->priceCalculation = $priceCalculation;
        $this->appConfig = $appConfig;
        $this->configResource = $configResource;
        $this->dateTime = $dateTime;
        $this->cacheClean = $cacheClean;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $request = $this->getRequest();

        if ($request->isXmlHttpRequest()) {
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $currentDateTime = $this->dateTime->gmtDate();

            /** PrivateSale Reindex */

            $entityToEventIndexer = $this->indexerRegistry->get(Indexer::INDEX_NAME);
            $entityToEventIndexer->reindexAll();

            $eventsToProductsIndexer = $this->indexerRegistry->get(Product::INDEXER_ID);
            $eventsToProductsIndexer->reindexAll();

            /** Recalculation Price and Catalog Product Price Reindex */
            $this->priceCalculation->recalculationPrice();
            /** Clear Frontend Cache */
            $this->cacheClean->cleanCache();

            $this->configResource->saveConfig(
                Config::CONFIG_SECTION . Config::LAST_FLUSH_DATE_PATH,
                $currentDateTime
            );

            $this->appConfig->reinit();
            $response = ['success' => true];

            return $resultJson->setData($response);
        }
    }
}
