<?php

namespace Magenest\AbandonedCart\Controller\Track;

use Magenest\AbandonedCart\Controller\Track;
use Magenest\AbandonedCart\Model\AbandonedCartFactory;
use Magenest\AbandonedCart\Model\Cron;
use Magenest\AbandonedCart\Model\LogContentFactory;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Encryption\Encryptor;
use Magento\Quote\Api\CartRepositoryInterface as CartRepositoryInterface;
use Magento\Quote\Model\QuoteFactory as QuoteModel;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magenest\AbandonedCart\Model\ResourceModel\LogContent as LogResource;
use Magenest\AbandonedCart\Model\ResourceModel\AbandonedCart as AbandonedCartResource;

class Restore extends Track
{
    /** @var Encryptor $_encryptor */
    protected $_encryptor;

    /** @var CustomerFactory $customerFactory */
    protected $customerFactory;

    /** @var CartRepositoryInterface $cartRepository */
    protected $cartRepository;

    /** @var AbandonedCartFactory $_abandonedCartFactory */
    protected $_abandonedCartFactory;

    /** @var LogContentFactory $_logContentFactory */
    protected $_logContentFactory;

    /** @var QuoteModel  */
    protected $_quoteModel;

    /** @var QuoteResource  */
    protected $_quoteResource;

    /** @var LogResource */
    protected $_logResource;

    protected $_abCartResource;

    /**
     * Restore constructor.
     *
     * @param Context $context
     * @param Session $checkoutSession
     * @param CustomerSession $customerSession
     * @param CartRepositoryInterface $cartRepository
     * @param CustomerFactory $customerFactory
     * @param Encryptor $encryptor
     * @param AbandonedCartFactory $abandonedCartFactory
     * @param LogContentFactory $logContentFactory
     * @param QuoteModel $quoteModel
     * @param QuoteResource $quoteResource
     * @param LogResource $logResource
     * @param AbandonedCartResource $abCartresource
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        CustomerSession $customerSession,
        CartRepositoryInterface $cartRepository,
        CustomerFactory $customerFactory,
        Encryptor $encryptor,
        AbandonedCartFactory $abandonedCartFactory,
        LogContentFactory $logContentFactory,
        QuoteModel $quoteModel,
        QuoteResource $quoteResource,
        LogResource $logResource,
        AbandonedCartResource $abCartresource
    ) {
        parent::__construct($context, $checkoutSession, $customerSession);
        $this->_encryptor = $encryptor;
        $this->customerFactory = $customerFactory;
        $this->cartRepository = $cartRepository;
        $this->_abandonedCartFactory = $abandonedCartFactory;
        $this->_logContentFactory = $logContentFactory;
        $this->_quoteModel = $quoteModel;
        $this->_quoteResource = $quoteResource;
        $this->_logResource = $logResource;
        $this->_abCartResource = $abCartresource;
    }

    public function execute()
    {
        $resumeRequest = $this->getRequest()->getParam('utc');
        $userAutoLoginKey = Cron::base64UrlDecode($this->getRequest()->getParam('u'));
        $cartId = $resumeRequest;
        try {
            $abandonedCartList = $this->_abandonedCartFactory->create();
            $this->_abCartResource->load($abandonedCartList, $cartId, 'quote_id');
            if (!$abandonedCartList->getPlaced()) {
                $quote = $this->_quoteModel->create();
                $this->_quoteResource->load($quote, $cartId, 'entity_id');
                if (!$this->checkoutSession) {
                    $this->checkoutSession = $this->_objectManager->create('\Magento\Checkout\Model\Session');
                }
                if ($quote->getReservedOrderId()) {
                    $quote = $this->_quoteModel->create()->merge($quote);
                    if ($this->checkoutSession->getQuote()) {
                        $this->checkoutSession->getQuote()->merge($quote);
                        $this->cartRepository->save($this->checkoutSession->getQuote());
                        $quote = $this->checkoutSession->getQuote();
                    } else {
                        $this->cartRepository->save($quote);
                    }
                }
                if ($userAutoLoginKey) {
                    if (!$this->customerSession->isLoggedIn()) {
                        $customerKey = $this->_encryptor->decrypt($userAutoLoginKey);
                        $customerData = explode("-", $customerKey);
                        $customerId = $customerData[0];
                        $customerEmail = $customerData[1];
                        $customer = $this->customerFactory->create()->load($customerId);
                        if ($customer->getId() && $customer->getEmail() === $customerEmail) {
                            $this->customerSession->setCustomerAsLoggedIn($customer);
                        }
                    }
                }

                $logId = $this->getRequest()->getParam('l');
                if ($logId) {
                    $logContent = $this->_logContentFactory->create();
                    $this->_logResource->load($logContent, $logId, 'id');
                    $logContent->setData('is_restore', 1);
                    $this->_logResource->save($logContent);
                    $this->checkoutSession->setData('ab_rule',$logContent->getData('rule_id'));
                }
                $this->checkoutSession->replaceQuote($quote);
            }
        } catch (\Exception $e) {
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('checkout/cart/index');
    }
}
