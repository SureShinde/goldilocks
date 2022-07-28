<?php

namespace Magenest\SocialLogin\Helper;

use Exception;
use Magenest\SocialLogin\Model\Config\DisplayOn;
use Magenest\SocialLogin\Model\Pinterest\Client;
use Magenest\SocialLogin\Model\ResourceModel\SocialAccount;
use Magenest\SocialLogin\Model\ResourceModel\SocialAccount\CollectionFactory;
use Magenest\SocialLogin\Model\SocialAccountFactory;
use Magento\Backend\App\ConfigInterface;
use Magento\Config\Model\Config;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\AccountConfirmation;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\SessionFactory;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class SocialLogin
 * @package Magenest\SocialLogin\Helper
 */
class SocialLogin extends AbstractHelper
{
    /**
     *
     */
    const REFERER_STORE_PARAM_NAME = 'sociallogin_referer_store';

    /**
     *
     */
    const XML_PATH_ENABLE_MODAL = 'magenest/general/enabled_social_enabled_modal';
    /**
     *
     */
    const XML_PATH_DISPLAY_ON = 'magenest/general/display_on';

    const ALLOW_TYPE = ["facebook", "google", "amazon", "instagram", "line", "linkedin", "reddit", "twitter", "zalo", "apple"];

    /**
     * @var SessionFactory
     */
    protected $_customerSession;
    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var Config
     */
    protected $_config;

    /**
     * @var CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var Url
     */
    protected $customerUrl;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer
     */
    protected $customerResource;

    /**
     * @var CollectionFactory
     */
    protected $socialAccountCollection;

    /**
     * @var SocialAccountFactory
     */
    protected $socialAccountModel;

    /**
     * @var SocialAccount
     */
    protected $socialAccountResource;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;
    /**
     * @var \Magenest\SocialLogin\Model\Line\Client
     */
    protected $clientLine;
    /**
     * @var \Magenest\SocialLogin\Model\Zalo\Client
     */
    protected $clientZalo;
    /**
     * @var \Magenest\SocialLogin\Model\Apple\Client
     */
    protected $clientApple;
    /**
     * @var \Magenest\SocialLogin\Model\Amazon\Client
     */
    protected $clientAmazon;
    /**
     * @var \Magenest\SocialLogin\Model\Google\Client
     */
    protected $clientGoogle;
    /**
     * @var \Magenest\SocialLogin\Model\Reddit\Client
     */
    protected $clientReddit;
    /**
     * @var \Magenest\SocialLogin\Model\Twitter\Client
     */
    protected $clientTwitter;
    /**
     * @var \Magenest\SocialLogin\Model\Facebook\Client
     */
    protected $clientFacebook;
    /**
     * @var \Magenest\SocialLogin\Model\Linkedin\Client
     */
    protected $clientLinkedin;
    /**
     * @var \Magenest\SocialLogin\Model\Instagram\Client
     */
    protected $clientInstagram;
    /**
     * @var Client
     */
    protected $clientPinterest;
    /**
     * @var array
     */
    protected $clients = [];

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var AccountManagement
     */
    protected $accountManagement;

    /**
     * @var MessageManagerInterface
     */
    protected $messageManager;

    /**
     * SocialLogin constructor.
     * @param \Magenest\SocialLogin\Model\Line\Client $clientLine
     * @param \Magenest\SocialLogin\Model\Zalo\Client $clientZalo
     * @param \Magenest\SocialLogin\Model\Apple\Client $clientApple
     * @param \Magenest\SocialLogin\Model\Amazon\Client $clientAmazon
     * @param \Magenest\SocialLogin\Model\Google\Client $clientGoogle
     * @param \Magenest\SocialLogin\Model\Reddit\Client $clientReddit
     * @param \Magenest\SocialLogin\Model\Twitter\Client $clientTwitter
     * @param \Magenest\SocialLogin\Model\Facebook\Client $clientFacebook
     * @param \Magenest\SocialLogin\Model\Linkedin\Client $clientLinkedin
     * @param \Magenest\SocialLogin\Model\Instagram\Client $clientInstagram
     * @param Client $clientPinterest
     * @param CustomerRepositoryInterface $customerRepository
     * @param SessionFactory $customerSession
     * @param CustomerFactory $customerFactory
     * @param StoreManagerInterface $storeManager
     * @param ConfigInterface $config
     * @param Context $context
     * @param CookieManagerInterface $cookieManager
     * @param Url $customerUrl
     * @param \Magento\Customer\Model\ResourceModel\Customer $customerResource
     * @param CollectionFactory $socialAccountCollection
     * @param SocialAccountFactory $socialAccountModel
     * @param SocialAccount $socialAccountResource
     * @param TimezoneInterface $timezone
     * @param AccountManagement $accountManagement
     * @param MessageManagerInterface $messageManager
     */
    public function __construct(
        \Magenest\SocialLogin\Model\Line\Client $clientLine,
        \Magenest\SocialLogin\Model\Zalo\Client $clientZalo,
        \Magenest\SocialLogin\Model\Apple\Client $clientApple,
        \Magenest\SocialLogin\Model\Amazon\Client $clientAmazon,
        \Magenest\SocialLogin\Model\Google\Client $clientGoogle,
        \Magenest\SocialLogin\Model\Reddit\Client $clientReddit,
        \Magenest\SocialLogin\Model\Twitter\Client $clientTwitter,
        \Magenest\SocialLogin\Model\Facebook\Client $clientFacebook,
        \Magenest\SocialLogin\Model\Linkedin\Client $clientLinkedin,
        \Magenest\SocialLogin\Model\Instagram\Client $clientInstagram,
        Client $clientPinterest,
        CustomerRepositoryInterface $customerRepository,
        SessionFactory $customerSession,
        CustomerFactory $customerFactory,
        StoreManagerInterface $storeManager,
        ConfigInterface $config,
        Context $context,
        CookieManagerInterface $cookieManager,
        Url $customerUrl,
        \Magento\Customer\Model\ResourceModel\Customer $customerResource,
        CollectionFactory $socialAccountCollection,
        SocialAccountFactory $socialAccountModel,
        SocialAccount $socialAccountResource,
        TimezoneInterface $timezone,
        AccountManagement $accountManagement,
        MessageManagerInterface $messageManager
    ) {
        $this->customerRepository = $customerRepository;
        $this->clientAmazon = $clientAmazon;
        $this->clientApple = $clientApple;
        $this->clientFacebook = $clientFacebook;
        $this->clientGoogle = $clientGoogle;
        $this->clientInstagram = $clientInstagram;
        $this->clientLine = $clientLine;
        $this->clientLinkedin = $clientLinkedin;
        $this->clientPinterest = $clientPinterest;
        $this->clientReddit = $clientReddit;
        $this->clientTwitter = $clientTwitter;
        $this->clientZalo = $clientZalo;
        $this->clients = [
            $clientAmazon,
            $clientApple,
            $clientFacebook,
            $clientGoogle,
            $clientInstagram,
            $clientLine,
            $clientLinkedin,
            $clientPinterest,
            $clientReddit,
            $clientTwitter,
            $clientZalo
        ];
        $this->_config = $config;
        $this->_customerSession = $customerSession->create();
        $this->_customerFactory = $customerFactory;
        $this->_storeManager = $storeManager;
        $this->cookieManager = $cookieManager;
        $this->customerUrl = $customerUrl;
        $this->customerResource = $customerResource;
        $this->socialAccountCollection = $socialAccountCollection;
        $this->socialAccountModel = $socialAccountModel;
        $this->socialAccountResource = $socialAccountResource;
        $this->timezone = $timezone;
        $this->accountManagement = $accountManagement;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }

    /**
     * Login and save with customer email
     *
     * @param Customer $customer
     * @param array $data
     */
    public function login($customer, $data)
    {
        $dataModel = $customer->getDataModel();
        $dataModel->setCustomAttribute('magenest_sociallogin_id', $data['magenest_sociallogin_id']);
        $dataModel->setCustomAttribute('magenest_sociallogin_type', $data['magenest_sociallogin_type']);
        $customer->updateData($dataModel)->save();
        $this->_customerSession->setCustomerAsLoggedIn($customer);
    }

    /**
     * @param array $data
     * @param string $pathConfigConfirmation
     * @return Customer
     * @throws AlreadyExistsException
     */
    public function creatingAccount($data, $pathConfigConfirmation): Customer
    {
        $customer = $this->_customerFactory->create();
        try {
            $customerId = $this->customerRepository->get($data['email'])->getId();
            $this->customerResource->load($customer, $customerId);
            if ($this->checkConditionConfirmationForSocialLoginFromSecondTime($pathConfigConfirmation, $customer)) {
                throw new EmailNotConfirmedException(__("You must confirm your account. Please check your email for the confirmation link."));
            }
            $this->_customerSession->setCustomerAsLoggedIn($customer);
        } catch (Exception $exception) {
            $customer->setData($data);
            $this->customerResource->save($customer);
            if ($this->checkConditionConfirmationForSocialLoginFirstTime($pathConfigConfirmation, $customer)) {
                try {
                    // Use this for email template NEW_ACCOUNT_EMAIL_CONFIRMATION of magento
                    $this->accountManagement->resendConfirmation($customer->getEmail(), $customer->getWebsiteId());
                    $this->messageManager->addComplexSuccessMessage(
                        'confirmAccountSuccessMessage',
                        [
                            'url' => $this->customerUrl->getEmailConfirmationUrl($customer->getEmail()),
                        ]
                    );
                } catch (Exception $exception) {
                    $this->_logger->debug($exception->getMessage());
                }
            } else {
                $this->_customerSession->setCustomerAsLoggedIn($customer);
            }
        }
        return $customer;
    }

    /**
     * @param $data
     * @throws AlreadyExistsException
     */
    public function createSocialAccount($data)
    {
        $currentDateTime = $this->timezone->date()->format('Y-m-d H:i:s');
        $socialAccount = $this->socialAccountModel->create();
        $socialAccount->setCustomerId($data['customerId'] ?? null);
        $socialAccount->setSocialLoginId($data['id'] ?? null);
        $socialAccount->setSocialLoginType($data['type'] ?? null);
        $socialAccount->setCreatedAt($currentDateTime);
        $socialAccount->setLastLogin($currentDateTime);
        $socialAccount->setSocialEmail($data['email'] ?? null);
        $socialAccount->setExistEmail($data['exist_email'] ?? null);
        $this->socialAccountResource->save($socialAccount);
    }

    /**
     * @param $data
     * @throws AlreadyExistsException
     */
    public function updateLastLoginTime($data)
    {
        $socialAccountCollection = $this->socialAccountCollection->create()->addFieldToFilter('social_login_id', $data['id'])
            ->addFieldToFilter('social_login_type', $data['type']);
        $socialAccount = $socialAccountCollection->getFirstItem();
        $socialAccount->setLastLogin($this->timezone->date()->format('Y-m-d H:i:s'));
        $socialAccount->setExistEmail($data['exist_email'] ?? null);
        $this->socialAccountResource->save($socialAccount);
    }

    /**
     * @param $socialLoginId
     * @param $socialLoginType
     * @return Customer
     */
    public function getCustomer($socialLoginId, $socialLoginType)
    {
        $socialAccountCollection = $this->socialAccountCollection->create()
            ->addFieldToFilter('social_login_id', $socialLoginId)
            ->addFieldToFilter('social_login_type', $socialLoginType);
        $customer = $this->_customerFactory->create();
        $this->customerResource->load($customer, $socialAccountCollection->getFirstItem()->getCustomerId());
        return $customer;
    }

    /**
     * Get Customer By Email
     *
     * @param $email
     * @return Customer
     * @throws LocalizedException
     */
    public function getCustomerByEmail($email)
    {
        $websiteId = $this->_storeManager->getWebsite()->getId();
        $customer = $this->_customerFactory->create()->setWebsiteId($websiteId)->loadByEmail($email);
        return $customer;
    }

    /**
     * @return Bool
     */
    public function isLoggedIn()
    {
        return $this->_customerSession->isLoggedIn();
    }

    /**
     * @return Bool
     */
    public function isButtonEnabledCreateAccount()
    {
        $displayOn = explode(',', $this->_config->getValue(self::XML_PATH_DISPLAY_ON));
        return in_array(DisplayOn::CREATE_ACCOUNT_PAGE, $displayOn);
    }

    /**
     * @return Bool
     */
    public function isButtonEnabledCheckout()
    {
        $displayOn = explode(',', $this->_config->getValue(self::XML_PATH_DISPLAY_ON));
        return in_array(DisplayOn::CHECKOUT_PAGE, $displayOn);
    }

    /**
     * @return Bool
     */
    public function isButtonEnabledCommentProduct()
    {
        $displayOn = explode(',', $this->_config->getValue(self::XML_PATH_DISPLAY_ON));
        return in_array(DisplayOn::COMMENT_PRODUCT, $displayOn);
    }

    /**
     * @return Bool
     */
    public function isButtonEnabledModal()
    {
        return (bool)$this->_config->getValue(self::XML_PATH_ENABLE_MODAL);
    }

    /**
     * @param $email
     * @return String
     */
    public function getTypeByEmail($email)
    {
        $customer = $this->getCustomerByEmail($email);
        return $customer['magenest_sociallogin_type'];
    }

    /**
     * @return string
     */
    public function getRedirectUrl()
    {
        $cookieRedirect = $this->cookieManager->getCookie(self::REFERER_STORE_PARAM_NAME);
        return $cookieRedirect ?? $this->customerUrl->getDashboardUrl();
    }

    /**
     * @return string[]
     */
    public function getAllSocialTypes(): array
    {
        $allSocialTypes = [];
        foreach ($this->clients as $client) {
            if ($client->isEnabled()) {
                $allSocialTypes[] = $client::TYPE_SOCIAL_LOGIN;
            }
        }
        return $allSocialTypes;
    }

    /**
     * @return array
     */
    public function getAllTypesNotCheckEnable(): array
    {
        $allSocialTypes = [];
        foreach ($this->clients as $client) {
            $allSocialTypes[] = $client::TYPE_SOCIAL_LOGIN;
        }
        return $allSocialTypes;
    }

    /**
     * @return array
     */
    public function getChartColor(): array
    {
        $chartColor = [];
        foreach ($this->clients as $client) {
            $chartColor[$client::TYPE_SOCIAL_LOGIN] = $client::CHART_COLOR;
        }
        return $chartColor;
    }

    /**
     * Check if accounts confirmation is required.
     * Check if accounts confirmation config og Social login module is required.
     *
     * @param string $path
     * @param int|null $websiteId
     * @param int|null $customerId
     * @return bool
     */
    public function isConfirmationSocialLoginRequired($path, $websiteId, $customerId): bool
    {
        if ($this->canSkipConfirmation($customerId)) {
            return false;
        }

        return $this->scopeConfig->isSetFlag(
            $path,
            ScopeInterface::SCOPE_WEBSITES,
            $websiteId
        );
    }

    /**
     * Check if accounts confirmation config og customer magento module is required.
     *
     * @param int|null $websiteId
     * @param int|null $customerId
     * @return bool
     */
    public function isConfirmationMagentoLoginRequired($websiteId, $customerId): bool
    {
        if ($this->canSkipConfirmation($customerId)) {
            return false;
        }

        return $this->scopeConfig->isSetFlag(
            AccountConfirmation::XML_PATH_IS_CONFIRM,
            ScopeInterface::SCOPE_WEBSITES,
            $websiteId
        );
    }

    /**
     * Check whether confirmation may be skipped when registering using certain email address.
     *
     * @param int|null $customerId
     * @return bool
     */
    private function canSkipConfirmation($customerId): bool
    {
        if (!$customerId) {
            return true;
        }

        return false;
    }

    /**
     * Function checkConditionConfirmationForSocialLoginFirstTime
     *
     * Used for check login confirmation from first time login
     *
     * @param string $pathConfigConfirmation
     * @param Customer $customer
     * @return bool
     */
    public function checkConditionConfirmationForSocialLoginFirstTime($pathConfigConfirmation, $customer): bool
    {
        return $this->isConfirmationSocialLoginRequired(
            $pathConfigConfirmation,
            $customer->getWebsiteId(),
            $customer->getId()
        ) &&
            $this->isConfirmationMagentoLoginRequired(
                $customer->getWebsiteId(),
                $customer->getId()
            );
    }

    /**
     * Function checkConditionConfirmationForSocialLoginFromSecondTime
     *
     * Used for check login confirmation from second time login
     *
     * @param string $pathConfigConfirmation
     * @param Customer $customer
     * @return bool
     */
    public function checkConditionConfirmationForSocialLoginFromSecondTime($pathConfigConfirmation, $customer): bool
    {
        return $this->checkConditionConfirmationForSocialLoginFirstTime($pathConfigConfirmation, $customer) && $customer->getConfirmation();
    }

    public function getAllTypeOptionsAcceptedForApi($type)
    {
        if (in_array($type, self::ALLOW_TYPE)) {
            return true;
        } else {
            return false;
        }
    }
}
