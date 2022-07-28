<?php

namespace Magenest\AdvancedLogin\Plugin\Customer\Controller\Account;

use Magento\Customer\Model\Session;

class EditPost
{
    /**
     * @var \Magenest\AdvancedLogin\Model\ConfigProvider
     */
    protected $configProvider;

    /**
     * @var Session
     */
    private $customerSession;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $_request;

    /**
     * EditPost constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param Session $customerSession
     * @param \Magenest\AdvancedLogin\Model\ConfigProvider $configProvider
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        Session $customerSession,
        \Magenest\AdvancedLogin\Model\ConfigProvider $configProvider
    ) {
        $this->customerSession = $customerSession;
        $this->_request        = $request;
        $this->configProvider  = $configProvider;
    }

    public function beforeExecute($subject)
    {
        $data = $this->_request->getParams();
        if (!$data['email']) {
            $emailSuffix   = $this->configProvider->getEmailSuffix();
            $data["email"] = $data["telephone"] . '@' . $emailSuffix;
        }
        unset($data["telephone"]);
        $this->_request->setParams($data);
    }
}
