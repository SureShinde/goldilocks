<?php

namespace Magenest\SocialLogin\Controller\SubmitAccount;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;

class CreateUser extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magenest\SocialLogin\Helper\SocialLogin
     */
    protected $_helper;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magenest\SocialLogin\Helper\SocialLogin $helperGoogle,
        PageFactory $resultPageFactory
    )
    {
        $this->_customerSession = $customerSession;
        $this->_helper = $helperGoogle;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        try{
            $params = $this->getRequest()->getParams();
            $dataUser = $this->_customerSession->getUserInfo();
            $dataUser['telephone'] = $params['telephone'];
            $dataUser['username'] = $params['username'];
            if(isset($dataUser['email'])){
                if($dataUser['email'] == ''){
                    $dataUser['email'] = $params['email'] != '' ? $params['email'] : str_replace(' ', '', $dataUser['username'])."@".$dataUser['magenest_sociallogin_type'].".com";
                }
            }
            $this->_helper->creatingAccount($dataUser);
        }catch(\Exception $e){
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->_customerSession->isLoggedIn()) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect->setPath('customer/account/');
        }else{
            $resultRedirect->setPath('*/*/index');
        }

        return $resultRedirect;
    }
}
