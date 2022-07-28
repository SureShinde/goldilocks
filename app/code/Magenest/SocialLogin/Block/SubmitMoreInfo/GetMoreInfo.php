<?php

namespace Magenest\SocialLogin\Block\SubmitMoreInfo;

use Magento\Framework\View\Element\Template;

class GetMoreInfo extends Template
{
    /**
     * @var \Magento\Framework\Url
     */
    protected $urlHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @param \Magento\Framework\Url $urlHelper
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Framework\Url $urlHelper,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->urlHelper = $urlHelper;
        $this->_customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * @return string|null
     */
    public function getPostActionUrl(){
        return $this->urlHelper->getUrl('sociallogin/submitaccount/createuser/');
    }

    /**
     * @return string|null
     */
    public function getBackUrl() {
        return $this->urlHelper->getUrl('customer/account/login/');
    }

    /**
     * @return int
     */
    public function getEmailUser() {
        $dataUser = $this->_customerSession->getUserInfo();
        return $dataUser['email'];
    }
}

