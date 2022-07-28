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

namespace Plumrocket\PrivateSale\Block;

use Magento\Framework\UrlInterface;
use Plumrocket\PrivateSale\Model\Config\Source\BackgroundStyle;

class Splashpage extends \Magento\Framework\View\Element\Template
{
    /**
     * Splash Page
     * @var \Plumrocket\PrivateSale\Model\Splashpage
     */
    protected $splashPage;

    /**
     * Logo
     * @var \Magento\Theme\Block\Html\Header\Logo
     */
    protected $logo;

    /**
     * Filter provider
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $filterProvider;

    /**
     * Customer session
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Json helper
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Plumrocket\PrivateSale\Helper\Data
     */
    protected $dataHelper;

    /**
     * Constructor
     * @param \Plumrocket\PrivateSale\Model\Splashpage $splashPage
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper ,
     * @param \Magento\Theme\Block\Html\Header\Logo $logo
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Plumrocket\PrivateSale\Helper\Data $dataHelper
     * @param array $data
     */
    public function __construct(
        \Plumrocket\PrivateSale\Model\Splashpage $splashPage,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Theme\Block\Html\Header\Logo $logo,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Plumrocket\PrivateSale\Helper\Data $dataHelper,
        $data = []
    ) {
        $this->splashPage = $splashPage;
        $this->filterProvider = $filterProvider;
        $this->logo = $logo;
        $this->jsonHelper = $jsonHelper;
        $this->customerSession = $customerSession;
        $this->dataHelper = $dataHelper;

        parent::__construct($context, $data);
    }

    /**
     * Retrieve base url
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * Retrieve media ur
     * @return string
     */
    public function getPubMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * Is enabled lauching soon mode
     * @return boolean
     */
    public function isEnabledLaunchingSoon()
    {
        return $this->splashPage->isEnabledLaunchingSoon();
    }

    /**
     * Get logo image URL
     *
     * @return string
     */
    public function getLogoSrc()
    {
        return $this->logo->getLogoSrc();
    }

    /**
     * Get logo text
     *
     * @return string
     */
    public function getLogoAlt()
    {
        return $this->logo->getLogoAlt();
    }

    /**
     * @return bool
     */
    public function isUserLogin()
    {
        return $this->splashPage->isEnabledLogin();
    }

    /**
     * @return bool
     */
    public function isUserRegistration()
    {
        return $this->splashPage->isEnabledRegistration();
    }

    /**
     * @return bool
     */
    public function isUserLoginAndRegistration()
    {
        return $this->splashPage->isUserLoginAndRegistration();
    }

    /**
     * Rertrieve images json
     * @return string
     */
    public function getImagesJson()
    {
        $images = $this->splashPage->getImages();
        return $this->jsonHelper->jsonEncode($images);
    }

    /**
     * Is splash page enabled
     * @return boolean
     */
    public function isEnabledPage()
    {
        return $this->splashPage->isEnabledRedirect();
    }

    /**
     * Retrieve post action url
     * @return string
     */
    public function getPostActionUrl()
    {
        return $this->getUrl('customer/ajax/login');
    }

    /**
     * Filter content
     * @param  string $content
     * @return string
     */
    public function filter($content = '')
    {
        return $this->filterProvider->getBlockFilter()->filter($content);
    }

    /**
     * Retrieve base url to media files
     * @return string
     */
    public function getSplashPageMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
            . DIRECTORY_SEPARATOR
            . 'splashpage';
    }

    /**
     * @return string
     */
    public function getRegistrationFormText()
    {
        return $this->splashPage->getFormText();
    }

    /**
     * @return string
     */
    public function getRegistrationConfirmationText()
    {
        return $this->splashPage->getConfirmationText();
    }

    /**
     * @return int
     */
    public function getBackroundType()
    {
        return (int) $this->splashPage->getStyle();
    }

    /**
     * @return array
     */
    public function getImagesUrl()
    {
        return $this->splashPage->getActiveImages();
    }

    /**
     * @return string
     */
    public function getVideoId()
    {
        $videos = $this->splashPage->getActiveVideos();

        if (! empty($videos)) {
            return $this->dataHelper->getVideoId($videos[array_rand($videos)]['url']);
        }

        return '';
    }

    /**
     * @return array|mixed|string
     */
    public function getSourceData()
    {
        switch ($this->getBackroundType()) {
            case BackgroundStyle::SLIDESHOW:
                return $this->getImagesUrl();
            case BackgroundStyle::VIDEO:
                return $this->getVideoUrl();
            default:
                $imageModel = $this->getImagesUrl();
                shuffle($imageModel);
                return $imageModel ? current($imageModel)->getUrl() : null;
        }
    }

    /**
     * @return bool
     */
    public function isVideoBackground()
    {
        return (int) $this->getBackroundType() === BackgroundStyle::VIDEO;
    }

    /**
     * @return bool
     */
    public function isSliderBackground()
    {
        return (int) $this->getBackroundType() === BackgroundStyle::SLIDESHOW;
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set($this->splashPage->getMetaTitle());
        $this->pageConfig->setDescription($this->splashPage->getMetaDescription());
        $this->pageConfig->setKeywords($this->splashPage->getMetaKeywords());
    }

    /**
     * @return |null
     */
    public function getMobileSplashPageImg()
    {
        $imageModel = $this->getImagesUrl();
        shuffle($imageModel);

        return $imageModel ? current($imageModel)->getUrl()
            : $this->getViewFileUrl('Plumrocket_PrivateSale::images/default_splashpage.jpg');
    }
}
