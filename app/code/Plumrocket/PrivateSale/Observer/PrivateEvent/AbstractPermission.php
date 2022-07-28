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

namespace Plumrocket\PrivateSale\Observer\PrivateEvent;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use Plumrocket\PrivateSale\Api\PrivateSaleServiceInterface;
use Plumrocket\PrivateSale\Model\Event\Catalog;
use Plumrocket\PrivateSale\Model\CatalogSession;
use Plumrocket\PrivateSale\Model\Integration\PopupLogin as PopupLoginModel;
use Plumrocket\PrivateSale\Model\Config\Source\Eventlandingpage;
use Magento\Customer\Model\SessionFactory;
use Plumrocket\PrivateSale\Helper\Config as ConfigHelper;

abstract class AbstractPermission implements ObserverInterface
{
    const REDIRECT_CODE = 302;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Catalog
     */
    protected $eventCatalog;

    /**
     * @var PrivateSaleServiceInterface
     */
    protected $privateSaleService;

    /**
     * @var CatalogSession
     */
    protected $catalogSession;

    /**
     * @var PopupLoginModel
     */
    private $popupLogin;

    /**
     * @var SessionFactory
     */
    private $session;

    /**
     * @var ConfigHelper
     */
    protected $config;

    /**
     * AbstractPermission constructor.
     * @param PopupLoginModel $popupLogin
     * @param PrivateSaleServiceInterface $privateSaleService
     * @param ProductRepositoryInterface $productRepository
     * @param Registry $registry
     * @param Catalog $eventCatalog
     * @param CatalogSession $catalogSession
     * @param SessionFactory $session
     * @param ConfigHelper $config
     */
    public function __construct(
        PopupLoginModel $popupLogin,
        PrivateSaleServiceInterface $privateSaleService,
        ProductRepositoryInterface $productRepository,
        Registry $registry,
        Catalog $eventCatalog,
        CatalogSession $catalogSession,
        SessionFactory $session,
        ConfigHelper $config
    ) {
        $this->privateSaleService = $privateSaleService;
        $this->productRepository = $productRepository;
        $this->registry = $registry;
        $this->eventCatalog = $eventCatalog;
        $this->catalogSession = $catalogSession;
        $this->popupLogin = $popupLogin;
        $this->session = $session;
        $this->config = $config;
    }

    /**
     * @param $event
     * @return bool
     */
    public function canRedirect($event): bool
    {
        return ! $this->popupLogin->isActive($event);
    }

    /**
     * @return int
     */
    public function getCustomerGroupId()
    {
        $customer = $this->session->create()->getCustomer();

        if ($customer && $customer->getId()) {
            return (int) $customer->getGroupId();
        }

        return 0;
    }
}
