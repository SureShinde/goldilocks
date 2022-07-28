<?php

namespace Magenest\AdvancedLogin\Plugin\Customer\Controller\Account;

use Acommerce\SmsIntegration\Helper\Data;
use Magenest\AdvancedLogin\Model\ConfigProvider;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\CookieManagerInterface;

class CreatePost
{
    /**
     * @var ConfigProvider
     */
    protected $configProvider;
    /**
     * @var Session
     */
    private $session;
    /**
     * @var ResultFactory
     */
    private $resultFactory;
    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var MessageManagerInterface
     */
    private $messageManager;
    /**
     * @var Data
     */
    private $smsHelper;

    /**
     * CreatePost constructor.
     * @param ConfigProvider $configProvider
     * @param Session $session
     * @param ResultFactory $resultFactory
     * @param Registry $registry
     * @param MessageManagerInterface $messageManager
     */
    public function __construct(
        ConfigProvider          $configProvider,
        Session                 $session,
        ResultFactory           $resultFactory,
        Registry                $registry,
        MessageManagerInterface $messageManager,
        Data $smsHelper
    ) {
        $this->configProvider = $configProvider;
        $this->session = $session;
        $this->resultFactory = $resultFactory;
        $this->registry = $registry;
        $this->messageManager = $messageManager;
        $this->smsHelper = $smsHelper;
    }

    /**
     * @param \Magento\Customer\Controller\Account\CreatePost $subject
     * @return \Magento\Customer\Controller\Account\CreatePost
     */
    public function beforeExecute(\Magento\Customer\Controller\Account\CreatePost $subject)
    {
        $data = $subject->getRequest()->getParams();
        if (isset($data['email']) && !$data['email']) {
            $emailSuffix = $this->configProvider->getEmailSuffix();
            $data['email'] = $data['telephone'] . '@' . $emailSuffix;
            $subject->getRequest()->setParams($data);
        }
        return $subject;
    }

    /**
     * @param \Magento\Customer\Controller\Account\CreatePost $subject
     * @param $result
     * @return Redirect|ResultInterface|mixed
     */
    public function afterExecute(\Magento\Customer\Controller\Account\CreatePost $subject, $result)
    {
        if ($this->registry->registry('customer_register_success')) {
            $this->messageManager->getMessages()->deleteMessageByIdentifier('confirmAccountSuccessMessage');
            $email = $subject->getRequest()->getParam('email');
            $this->session->setData('customer_email', $email);
            $this->session->setData('is_social_login', false);
            $telephone = $subject->getRequest()->getParam('telephone');
            $this->smsHelper->sendSmsOTP($telephone);
            $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $redirect->setUrl('/advancedlogin/otp/index');
            return $redirect;
        }
        return $result;
    }
}
