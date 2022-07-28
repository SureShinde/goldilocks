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

namespace Plumrocket\PrivateSale\Observer\Checkout\Cart;

use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Checkout\Model\SessionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Plumrocket\PrivateSale\Helper\Config as ConfigHelper;
use Plumrocket\PrivateSale\Model\Frontend\QtyLimit;

class BeforeAddToCart implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var ConfigHelper
     */
    private $config;

    /**
     * @var QtyLimit
     */
    private $limit;

    /**
     * @var SessionFactory
     */
    private $sessionFactory;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var Configurable
     */
    private $configurable;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * BeforeAddToCart constructor.
     *
     * @param RequestInterface                                     $request
     * @param ManagerInterface                                     $messageManager
     * @param ConfigHelper                                         $config
     * @param QtyLimit                                             $limit
     * @param SessionFactory                                       $sessionFactory
     * @param ProductRepository                                    $productRepository
     * @param Configurable                                         $configurable
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     */
    public function __construct(
        RequestInterface $request,
        ManagerInterface $messageManager,
        ConfigHelper $config,
        QtyLimit $limit,
        SessionFactory $sessionFactory,
        ProductRepository $productRepository,
        Configurable $configurable,
        StockRegistryInterface $stockRegistry
    ) {
        $this->config = $config;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->limit = $limit;
        $this->sessionFactory = $sessionFactory;
        $this->productRepository = $productRepository;
        $this->configurable = $configurable;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (! $this->config->isModuleEnabled()) {
            return $this;
        }

        $request = $observer->getRequest();
        $productId = $request->getParam('product');

        $stockItem = $this->stockRegistry->getStockItem($productId);
        if (! $stockItem->getManageStock() || $stockItem->getBackorders()) {
            return $this;
        }

        $addQty = $request->getParam('qty') ?? 1;
        $qtyLimitCount = $this->limit->availableItemsCount($this->getRealSimpleProductId($request));
        $quote = $this->sessionFactory->create()->getQuote();

        foreach ($quote->getAllItems() as $item) {
            if ($item->getProductId() == $productId) {
                $addQty += $item->getQty();
            }
        }

        if (null !== $qtyLimitCount && $addQty > $qtyLimitCount) {
            $observer->getRequest()->setParam('product', false);
            $observer->getRequest()->setParam('return_url', false);
            $this->messageManager->addErrorMessage(__('Sorry, this product is sold out.'));
        }
    }

    /**
     * @param $request
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getRealSimpleProductId($request)
    {
        $productId = $request->getParam('product');
        $product = $this->productRepository->getById($productId);

        if ($product->getTypeId() === Configurable::TYPE_CODE) {
            $supperAttribute = $request->getParam('super_attribute');
            $simpleProduct = $this->configurable->getProductByAttributes($supperAttribute, $product);

            if ($simpleProduct) {
                $productId = $simpleProduct->getId();
            }
        }

        return $productId;
    }
}
