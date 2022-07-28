<?php

namespace Magenest\SocialLogin\Block\PopupModal;

use Magento\Framework\View\Element\Template;
use Magento\ReCaptchaUi\Model\UiConfigResolverInterface;
use Magento\Framework\Serialize\SerializerInterface;
/**
 * Class ModalContent
 * @package Magenest\SocialLogin\Block\PopupModal
 */
class ModalContent extends Template
{
    CONST RECAPTCHA_CONFIG = 'recaptcha_frontend/type_for/customer_login';
    /**
     * @var SerializerInterface
     */
    protected $serializer;
    /**
     * @var UiConfigResolverInterface
     */
    protected $captchaUiConfigResolver;
    /**
     * @var array|\Magento\Checkout\Block\Checkout\LayoutProcessorInterface[]
     */
    protected $layoutProcessors;
    /**
     * @var \Magenest\SocialLogin\Model\Twitter\Client
     */
    protected $_clientTwitter;
    /**
     * @var \Magenest\SocialLogin\Model\Facebook\Client
     */
    protected $_clientFacebook;
    /**
     * @var \Magenest\SocialLogin\Model\Google\Client
     */
    protected $_clientGoogle;
    /**
     * @var \Magenest\SocialLogin\Model\Amazon\Client
     */
    protected $_clientAmazon;
    /**
     * @var \Magenest\SocialLogin\Model\Instagram\Client
     */
    protected $_clientInstagram;
    /**
     * @var \Magenest\SocialLogin\Model\Reddit\Client
     */
    protected $_clientReddit;
    /**
     * @var \Magenest\SocialLogin\Model\Line\Client
     */
    protected $_clientLine;
    /**
     * @var \Magenest\SocialLogin\Model\Pinterest\Client
     */
    protected $_clientPinterest;
    /**
     * @var \Magenest\SocialLogin\Model\Linkedin\Client
     */
    protected $_clientLinkedIn;
    /**
     * @var \Magenest\SocialLogin\Model\Zalo\Client
     */
    protected $_clientZalo;
    /**
     * @var \Magenest\SocialLogin\Model\Apple\Client
     */
    protected $_clientApple;
    /**
     * @var \Magenest\SocialLogin\Helper\SocialLogin
     */
    protected $_sociallogin;

    /**
     * ModalContent constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magenest\SocialLogin\Model\Twitter\Client $clientTwitter
     * @param \Magenest\SocialLogin\Model\Facebook\Client $clientFacebook
     * @param \Magenest\SocialLogin\Model\Google\Client $clientGoogle
     * @param \Magenest\SocialLogin\Model\Amazon\Client $clientAmazon
     * @param \Magenest\SocialLogin\Model\Instagram\Client $clientInstagram
     * @param \Magenest\SocialLogin\Model\Reddit\Client $clientReddit
     * @param \Magenest\SocialLogin\Model\Line\Client $clientLine
     * @param \Magenest\SocialLogin\Model\Pinterest\Client $clientPinterest
     * @param \Magenest\SocialLogin\Model\Linkedin\Client $clientLinkedIn
     * @param \Magenest\SocialLogin\Model\Zalo\Client $clientZalo
     * @param \Magenest\SocialLogin\Model\Apple\Client $clientApple
     * @param \Magenest\SocialLogin\Helper\SocialLogin $socialLogin
     * @param UiConfigResolverInterface $captchaUiConfigResolver
     * @param SerializerInterface $serializer
     * @param array $layoutProcessors
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magenest\SocialLogin\Model\Twitter\Client $clientTwitter,
        \Magenest\SocialLogin\Model\Facebook\Client $clientFacebook,
        \Magenest\SocialLogin\Model\Google\Client $clientGoogle,
        \Magenest\SocialLogin\Model\Amazon\Client $clientAmazon,
        \Magenest\SocialLogin\Model\Instagram\Client $clientInstagram,
        \Magenest\SocialLogin\Model\Reddit\Client $clientReddit,
        \Magenest\SocialLogin\Model\Line\Client $clientLine,
        \Magenest\SocialLogin\Model\Pinterest\Client $clientPinterest,
        \Magenest\SocialLogin\Model\Linkedin\Client $clientLinkedIn,
        \Magenest\SocialLogin\Model\Zalo\Client $clientZalo,
        \Magenest\SocialLogin\Model\Apple\Client $clientApple,
        \Magenest\SocialLogin\Helper\SocialLogin $socialLogin,
        UiConfigResolverInterface $captchaUiConfigResolver,
        SerializerInterface $serializer,
        array $layoutProcessors = [],
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->jsLayout         = isset($data['jsLayout']) && is_array($data['jsLayout']) ? $data['jsLayout'] : [];
        $this->layoutProcessors = $layoutProcessors;
        $this->_clientTwitter   = $clientTwitter;
        $this->_clientFacebook  = $clientFacebook;
        $this->_clientGoogle    = $clientGoogle;
        $this->_clientAmazon    = $clientAmazon;
        $this->_clientInstagram = $clientInstagram;
        $this->_clientLine      = $clientLine;
        $this->_clientLinkedIn  = $clientLinkedIn;
        $this->_clientPinterest = $clientPinterest;
        $this->_clientReddit    = $clientReddit;
        $this->_clientZalo      = $clientZalo;
        $this->_clientApple     = $clientApple;
        $this->_sociallogin     = $socialLogin;
        $this->captchaUiConfigResolver = $captchaUiConfigResolver;
        $this->serializer = $serializer;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\InputException
     */
    public function getJsLayout()
    {
        if($this->_scopeConfig->getValue(ModalContent::RECAPTCHA_CONFIG) != null){
            $key = 'recaptcha-' . sha1($this->getNameInLayout());
            $config_key = $this->getData('recaptcha_for');
            $uiConfig = $this->captchaUiConfigResolver->get($config_key);

            $layout = $this->serializer->unserialize(parent::getJsLayout());
            if (isset($layout['components']['modal_content']['children']['recaptcha'])) {
                $layout['components']['modal_content']['children'][$key]
                    = $layout['components']['modal_content']['children']['recaptcha'];
                unset($layout['components']['modal_content']['children']['recaptcha']);
            }
            $layout['components']['modal_content']['children'][$key] = array_replace_recursive(
                [
                    'settings' => $uiConfig,
                ],
                $layout['components']['modal_content']['children'][$key]
            );
            $layout['components']['modal_content']['children'][$key]['reCaptchaId'] = $key;
            $this->jsLayout = $layout;
        }else{
            $jsLayout = $this->jsLayout;
            $jsLayout['components']['modal_content']['children'] = [];
            $this->jsLayout = $jsLayout;
            $this->setData('jsLayout', []);
        }
        foreach ($this->layoutProcessors as $processor) {
            $this->jsLayout = $processor->process($this->jsLayout);
        }
        return \Zend_Json::encode($this->jsLayout);
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            'baseUrl'                  => $this->getBaseUrl(),
            'isButtonEnabledCheckout'  => $this->isButtonEnabledCheckout(),
            'isEnabledPopup'           => $this->isEnabledPopup(),
            'isEnabledInCreateAccount' => $this->isEnabledInCreateAccount(),
            'TwitterUrl'               => $this->getTwitterUrl(),
            'isTwitterEnabled'         => $this->isTwitterEnabled(),
            'FacebookUrl'              => $this->getFacebookUrl(),
            'isFacebookEnabled'        => $this->isFacebookEnabled(),
            'GoogleUrl'                => $this->getGoogleUrl(),
            'isGoogleEnabled'          => $this->isGoogleEnabled(),
            'AmazonUrl'                => $this->getAmazonUrl(),
            'isAmazonEnabled'          => $this->isAmazonEnabled(),
            'InstagramUrl'             => $this->getInstagramUrl(),
            'isInstagramEnabled'       => $this->isInstagramEnabled(),
            'LineUrl'                  => $this->getLineUrl(),
            'isLineEnabled'            => $this->isLineEnabled(),
            'PinterestUrl'             => $this->getPinterestUrl(),
            'isPinterestEnabled'       => $this->isPinterestEnabled(),
            'RedditUrl'                => $this->getRedditUrl(),
            'isRedditEnabled'          => $this->isRedditEnabled(),
            'LinkedInUrl'              => $this->getLinkedInUrl(),
            'AppleUrl'                 => $this->getAppleUrl(),
            'isAppleEnabled'           => $this->isAppleEnabled(),
            'isLinkedInEnabled'        => $this->isLinkedInEnabled(),
            'isZaloEnabled'            => $this->isZaloEnabled(),
            'ZaloUrl'                  => $this->isZaloEnabled() ? $this->getZaloUrl() : "",
            'customerRegisterUrl' => $this->escapeUrl($this->getCustomerRegisterUrlUrl()),
            'customerForgotPasswordUrl' => $this->escapeUrl($this->getCustomerForgotPasswordUrl()),
        ];
    }

    /**
     * @return string
     */
    public function getFacebookUrl()
    {
        return $this->_clientFacebook->createAuthUrl();
    }

    /**
     * @return string
     */
    public function getTwitterUrl()
    {
        return $this->_clientTwitter->createAuthUrl();
    }

    /**
     * @return string
     */
    public function getGoogleUrl()
    {
        return $this->_clientGoogle->createAuthUrl();
    }

    /**
     * @return string
     */
    public function getAmazonUrl()
    {
        return $this->_clientAmazon->createAuthUrl();
    }

    /**
     * @return string
     */
    public function getLinkedInUrl()
    {
        return $this->_clientLinkedIn->createAuthUrl();
    }

    /**
     * @return string
     */
    public function getInstagramUrl()
    {
        return $this->_clientInstagram->createAuthUrl();
    }

    /**
     * @return string
     */
    public function getRedditUrl()
    {
        return $this->_clientReddit->createAuthUrl();
    }

    /**
     * @return string
     */
    public function getPinterestUrl()
    {
        return $this->_clientPinterest->createAuthUrl();
    }

    /**
     * @return string
     */
    public function getLineUrl()
    {
        return $this->_clientLine->createAuthUrl();
    }

    /**
     * @return string|void
     */
    public function getZaloUrl()
    {
        return $this->_clientZalo->createAuthUrl();
    }

    /**
     * @return string|void
     */
    public function getAppleUrl()
    {
        return $this->_clientApple->createAuthUrl();
    }

    /**
     * @return bool
     */
    public function isLinkedInEnabled()
    {
        return $this->_clientLinkedIn->isEnabled();
    }

    /**
     * @return bool
     */
    public function isInstagramEnabled()
    {
        return $this->_clientInstagram->isEnabled();
    }

    /**
     * @return bool
     */
    public function isRedditEnabled()
    {
        return $this->_clientReddit->isEnabled();
    }

    /**
     * @return bool
     */
    public function isPinterestEnabled()
    {
        return $this->_clientPinterest->isEnabled();
    }

    /**
     * @return bool
     */
    public function isLineEnabled()
    {
        return $this->_clientLine->isEnabled();
    }

    /**
     * @return bool
     */
    public function isTwitterEnabled()
    {
        return $this->_clientTwitter->isEnabled();
    }

    /**
     * @return bool
     */
    public function isFacebookEnabled()
    {
        return $this->_clientFacebook->isEnabled();
    }

    /**
     * @return bool
     */
    public function isGoogleEnabled()
    {
        return $this->_clientGoogle->isEnabled();
    }

    /**
     * @return bool
     */
    public function isAmazonEnabled()
    {
        return $this->_clientAmazon->isEnabled();
    }

    /**
     * @return bool
     */
    public function isAppleEnabled()
    {
        return $this->_clientApple->isEnabled();
    }

    /**
     * @return bool
     */
    public function isZaloEnabled()
    {
        return $this->_clientZalo->isEnabled();
    }

    /**
     * @return bool
     */
    public function isButtonEnabledCheckout()
    {
        return $this->_sociallogin->isButtonEnabledCheckout();
    }

    /**
     * @return bool
     */
    public function isEnabledPopup()
    {
        return $this->_sociallogin->isButtonEnabledModal();
    }

    /**
     * @return bool
     */
    public function isEnabledInCreateAccount()
    {
        return $this->_sociallogin->isButtonEnabledCreateAccount();
    }
    /**
     * Return base url.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * Get customer register url
     *
     * @return string
     */
    public function getCustomerRegisterUrlUrl()
    {
        return $this->getUrl('customer/account/create');
    }

    /**
     * Get customer forgot password url
     *
     * @return string
     */
    public function getCustomerForgotPasswordUrl()
    {
        return $this->getUrl('customer/account/forgotpassword');
    }
}
