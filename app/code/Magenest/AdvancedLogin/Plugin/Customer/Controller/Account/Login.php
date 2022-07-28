<?php

namespace Magenest\AdvancedLogin\Plugin\Customer\Controller\Account;

use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\RequestInterface;

class Login
{
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_session;

    protected $_request;

    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $session,
        RequestInterface $request
    ) {
        $this->_session = $session;
        $this->_request = $request;
    }

    public function afterExecute(\Magento\Customer\Controller\Account\Login $subject, $result)
    {
        $referer = $this->_request->getParam(CustomerUrl::REFERER_QUERY_PARAM_NAME);
        $this->_session->setRefererParam($referer);
        return $result;
    }
}
