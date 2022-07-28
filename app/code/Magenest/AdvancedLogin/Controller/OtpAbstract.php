<?php

namespace Magenest\AdvancedLogin\Controller;

use Acommerce\SmsIntegration\Helper\Data;
use Magenest\AdvancedLogin\Helper\Otp;
use Magenest\AdvancedLogin\Helper\PhoneHelper;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Helper\Address;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Math\Random;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;

abstract class OtpAbstract extends Action
{
    /**
     * @var Session
     */
    protected $customerSession;
    /**
     * @var CustomerFactory
     */
    protected $customerFactory;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;
    /**
     * @var CustomerInterfaceFactory
     */
    protected $customerInterfaceFactory;
    /**
     * @var AccountManagementInterface
     */
    protected $accountManagement;
    /**
     * @var Url
     */
    protected $customerUrl;
    /**
     * @var UrlInterface
     */
    protected $urlModel;
    /**
     * @var AccountRedirect
     */
    protected $accountRedirect;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var PhpCookieManager|mixed
     */
    protected $cookieMetadataManager;
    /**
     * @var CookieMetadataFactory|mixed
     */
    protected $cookieMetadataFactory;
    /**
     * @var RawFactory
     */
    protected $rawFactory;
    /**
     * @var PageFactory
     */
    protected $pageFactory;
    /**
     * @var Address
     */
    protected $addressHelper;
    /**
     * @var Data
     */
    protected $smsHelper;
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;
    /**
     * @var PhoneHelper
     */
    protected $phoneHelper;
    /**
     * @var CustomerRegistry
     */
    protected $customerRegistry;
    /**
     * @var AccountManagement
     */
    protected $account;
    /**
     * @var Random
     */
    protected $mathRandom;
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param CustomerFactory $customerFactory
     * @param StoreManagerInterface $storeManager
     * @param DataObjectHelper $dataObjectHelper
     * @param CustomerInterfaceFactory $customerInterfaceFactory
     * @param AccountManagementInterface $accountManagement
     * @param Url $customerUrl
     * @param UrlInterface $urlModel
     * @param AccountRedirect $accountRedirect
     * @param ScopeConfigInterface $scopeConfig
     * @param RawFactory $rawFactory
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context                               $context,
        Session                               $customerSession,
        CustomerFactory                       $customerFactory,
        StoreManagerInterface                 $storeManager,
        DataObjectHelper                      $dataObjectHelper,
        CustomerInterfaceFactory              $customerInterfaceFactory,
        AccountManagementInterface            $accountManagement,
        Url                                   $customerUrl,
        UrlInterface                          $urlModel,
        AccountRedirect                       $accountRedirect,
        ScopeConfigInterface                  $scopeConfig,
        RawFactory                            $rawFactory,
        PageFactory                           $pageFactory,
        Address                               $addressHelper,
        Data $smsHelper,
        JsonFactory $jsonFactory,
        PhoneHelper $phoneHelper,
        CustomerRegistry $customerRegistry,
        AccountManagement $account,
        Random $mathRandom,
        CustomerRepositoryInterface $customerRepository
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->customerFactory = $customerFactory;
        $this->storeManager = $storeManager;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->customerInterfaceFactory = $customerInterfaceFactory;
        $this->accountManagement = $accountManagement;
        $this->customerUrl = $customerUrl;
        $this->urlModel = $urlModel;
        $this->accountRedirect = $accountRedirect;
        $this->scopeConfig = $scopeConfig;
        $this->rawFactory = $rawFactory;
        $this->pageFactory = $pageFactory;
        $this->addressHelper = $addressHelper;
        $this->smsHelper = $smsHelper;
        $this->jsonFactory = $jsonFactory;
        $this->phoneHelper = $phoneHelper;
        $this->customerRegistry = $customerRegistry;
        $this->account = $account;
        $this->mathRandom = $mathRandom;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        $resultRedirect = $this->resultRedirectFactory->create();
        $url = $this->urlModel->getUrl('*/*/*', ['_secure' => true]);
        $resultRedirect->setUrl($this->_redirect->error($url));

        return new InvalidRequestException(
            $resultRedirect,
            [new Phrase('Invalid Form Key. Please refresh the page.')]
        );
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return null;
    }

    public function execute()
    {
        if ($this->customerSession->isLoggedIn() || !$this->customerSession->getData('customer_email')) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $url = $this->urlModel->getUrl('noroute');
            $resultRedirect->setUrl($this->_redirect->error($url));
            return $resultRedirect;
        } else {
            return $this->pageFactory->create();
        }
    }
}
