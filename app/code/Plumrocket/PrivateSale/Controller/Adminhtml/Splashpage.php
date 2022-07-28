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
 * @copyright   Copyright (c) 2016 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\PrivateSale\Controller\Adminhtml;

use Magento\Backend\App\Action;

abstract class Splashpage extends Action
{
    const ADMIN_RESOURCE = 'Plumrocket_PrivateSale::privatesale';

    /**
     * SplashPageImage
     * @var \Plumrocket\PrivateSale\Model\SplashPageImageFactory
     */
    protected $imageFactory;

    /**
     * Config
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $config;

    /**
     * Json helper
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * App config
     * @var \Magento\Framework\App\Config\ReinitableConfigInterface
     */
    protected $appConfig;

    /**
     * Splashpage
     * @var \Plumrocket\PrivateSale\Model\Splashpage
     */
    protected $splashpage;

    /**
     * Registry
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * Splashpage constructor.
     *
     * @param \Plumrocket\PrivateSale\Model\SplashPageImageFactory $imageFactory
     * @param \Plumrocket\PrivateSale\Model\Splashpage $splashpage
     * @param \Magento\Config\Model\ResourceModel\Config $config
     * @param Action\Context $context
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface $appConfig
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Plumrocket\PrivateSale\Model\SplashPageImageFactory $imageFactory,
        \Plumrocket\PrivateSale\Model\Splashpage $splashpage,
        \Magento\Config\Model\ResourceModel\Config $config,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\App\Config\ReinitableConfigInterface $appConfig,
        \Magento\Framework\Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->imageFactory = $imageFactory;
        $this->config = $config;
        $this->appConfig = $appConfig;
        $this->splashpage = $splashpage;
        $this->serializer = $serializer;
    }
}
