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

namespace Plumrocket\PrivateSale\Model;

use Magento\Config\Model\ResourceModel\Config;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Plumrocket\PrivateSale\Model\Config\Source\SplashPage as SplashPageOptions;
use Plumrocket\PrivateSale\Model\Config\Source\SplashPageAccess;
use Plumrocket\PrivateSale\Model\ResourceModel\SplashPageImage\CollectionFactory;

class Splashpage extends AbstractModel
{
    const CONFIG_PATH = 'prprivatesale/splashpage/data';

    /**
     * Sub directory for media
     *
     * @var string
     */
    const IMAGE_DIR = 'plumrocket/privatesale/splashpage';

    /**
     * Dir for temporary images
     */
    const IMAGE_TMP_DIR = 'plumrocket/privatesale/tmp';

    /**
     * Data
     * @var array
     */
    private $sData;

    /**
     * @var UrlInterace
     */
    private $urlBuilder;

    /**
     * @var CustomerUrl
     */
    private $customerUrl;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var CurrentDateTime
     */
    private $currentDateTime;

    /**
     * @var CollectionFactory
     */
    private $splashPageImageFactory;

    /**
     * @var \Plumrocket\PrivateSale\Helper\Config
     */
    private $config;

    /**
     * @param \Magento\Framework\Model\Context                                              $context
     * @param \Magento\Framework\Registry                                                   $registry
     * @param \Plumrocket\PrivateSale\Model\ResourceModel\SplashPageImage\CollectionFactory $splashPageImageFactory
     * @param \Magento\Framework\Serialize\SerializerInterface                              $serializer
     * @param \Plumrocket\PrivateSale\Model\CurrentDateTime                                 $currentDateTime
     * @param \Magento\Framework\UrlInterface                                               $urlBuilder
     * @param \Magento\Customer\Model\Url                                                   $customerUrl
     * @param \Plumrocket\PrivateSale\Helper\Config                                         $config
     * @param array                                                                         $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CollectionFactory $splashPageImageFactory,
        SerializerInterface $serializer,
        CurrentDateTime $currentDateTime,
        UrlInterface $urlBuilder,
        CustomerUrl $customerUrl,
        \Plumrocket\PrivateSale\Helper\Config $config,
        array $data = []
    ) {
        $this->serializer = $serializer;
        $this->currentDateTime = $currentDateTime;
        $this->splashPageImageFactory = $splashPageImageFactory;
        $this->urlBuilder = $urlBuilder;
        $this->customerUrl = $customerUrl;
        $this->config = $config;

        parent::__construct($context, $registry, null, null, $data);
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        parent::_init(Config::class);
    }

    /**
     * @inheritDoc
     */
    public function getData($key = '', $index = null)
    {
        if (empty($this->sData)) {
            $data = $this->config->getConfig('/splashpage/data', $this->getStoreId(), $this->getScope());
            $data = ($data == null) ? [] : $this->serializer->unserialize($data);
            $this->setData($data);
            $this->sData = $data;
        }

        return parent::getData($key, $index);
    }

    /**
     * Is enabled redirect to login page
     * @return boolean
     */
    public function isEnabledRedirect()
    {
        return (bool) $this->getData('enabled');
    }

    /**
     * Does must be shown default image
     * @return boolean
     */
    public function showDefaultImage()
    {
        return ! $this->splashPageImageFactory->create()->getSize();
    }

    /**
     * Retrieve images
     * @return array
     */
    public function getActiveImages()
    {
        $currentDate = $this->currentDateTime->getCurrentDate();

        return $this->splashPageImageFactory
            ->create()
            ->addFieldToFilter('exclude', 0)
            ->addFieldToFilter(['active_from', 'active_from'], [['null' => true], ['lt' => $currentDate]])
            ->addFieldToFilter(['active_to', 'active_to'], [['null' => true], ['gt' => $currentDate]])
            ->setOrder('sort_order', 'ASC')
            ->getItems();
    }

    /**
     * @return array
     */
    public function getActiveVideos()
    {
        $videos = (array) $this->getData('videos');
        $currentTimestamp = $this->currentDateTime->getCurrentDate()->getTimestamp();

        foreach ($videos as $key => $video) {
            $activeFromTimestamp = strtotime($video['active_from']);
            $activeToTimestamp = $video['active_to'] ? strtotime($video['active_to']) : null;

            if ($activeFromTimestamp > $currentTimestamp
                || ($activeToTimestamp && $currentTimestamp > $activeToTimestamp)
                || '1' === $video['exclude']
            ) {
                unset($videos[$key]);
            }
        }

        return $videos;
    }

    /**
     * @return array
     */
    public function getImages()
    {
        return $this->splashPageImageFactory->create()->getItems();
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        $data = is_array($this->getData()) ? $this->serializer->serialize($this->getData()) : $this->getData();
        $this->getResource()->saveConfig(Splashpage::CONFIG_PATH, $data, $this->getScope(), $this->getStoreId());
    }

    /**
     * @return string
     */
    public function getLandingPageUrl()
    {
        switch ($this->getData('landing_page')) {
            case SplashPageOptions::MAGENTO_REGISTRATION_PAGE:
                $url = $this->customerUrl->getRegisterUrl();
                break;
            case SplashPageOptions::MAGENTO_LOGIN_PAGE:
                $url = $this->customerUrl->getLoginUrl();
                break;
            default:
                $url = $this->urlBuilder->getUrl(
                    'prprivatesale/splashpage/login',
                    $this->customerUrl->getLoginUrlParams()
                );
        }

        return $url;
    }

    /**
     * @return bool
     */
    public function isEnabledLaunchingSoon()
    {
        return (int) $this->getData('access') === SplashPageAccess::REGISTER;
    }

    /**
     * @return bool
     */
    public function isEnabledLogin()
    {
        $access = (int) $this->getData('access');
        return SplashPageAccess::LOGIN === $access || SplashPageAccess::LOGIN_AND_REGISTER === $access;
    }

    /**
     * @return bool
     */
    public function isEnabledRegistration()
    {
        $access = (int) $this->getData('access');
        return SplashPageAccess::REGISTER === $access || SplashPageAccess::LOGIN_AND_REGISTER === $access;
    }

    /**
     * @return bool
     */
    public function isUserLoginAndRegistration()
    {
        return (int) $this->getData('access') === SplashPageAccess::LOGIN_AND_REGISTER;
    }

    /**
     * @return string
     */
    private function getScope()
    {
        return isset($this->_data['admin'], $this->_data['store_id'])
            ? ScopeInterface::SCOPE_STORE
            : ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
    }

    /**
     * @return int
     */
    private function getStoreId()
    {
        return isset($this->_data['admin'], $this->_data['store_id'])
            ? (int) $this->_data['store_id']
            : Store::DEFAULT_STORE_ID;
    }
}
