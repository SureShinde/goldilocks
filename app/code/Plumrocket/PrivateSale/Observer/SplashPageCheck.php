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
 * @copyright   Copyright (c) 2016 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */
namespace Plumrocket\PrivateSale\Observer;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;
use Plumrocket\PrivateSale\Model\Config\Source\SplashPage;

class SplashPageCheck implements ObserverInterface
{
    /**
     * Preview helper
     * @var \Plumrocket\PrivateSale\Helper\Preview
     */
    protected $previewHelper;

    /**
     * Customer Session
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * Splashpage
     * @var \Plumrocket\PrivateSale\Model\Splashpage
     */
    protected $splashpage;

    /**
     * Url
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * Customer session
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $_actionFlag;

    /**
     * @var \Plumrocket\PrivateSale\Helper\Config
     */
    protected $configHelper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    private $response;

    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Plumrocket\PrivateSale\Helper\Preview      $previewHelper
     * @param \Magento\Customer\Model\Session             $customerSession
     * @param \Magento\Framework\App\ActionFlag           $actionFlag
     * @param \Magento\Framework\UrlInterface             $url
     * @param \Plumrocket\PrivateSale\Helper\Config       $configHelper
     * @param \Plumrocket\PrivateSale\Model\Splashpage    $splashpage
     * @param \Magento\Framework\App\RequestInterface     $request
     * @param \Magento\Framework\App\ResponseInterface    $response
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Plumrocket\PrivateSale\Helper\Preview $previewHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\UrlInterface $url,
        \Plumrocket\PrivateSale\Helper\Config $configHelper,
        \Plumrocket\PrivateSale\Model\Splashpage $splashpage,
        RequestInterface $request,
        \Magento\Framework\App\ResponseInterface $response
    ) {
        $this->splashpage = $splashpage;
        $this->messageManager = $messageManager;
        $this->previewHelper = $previewHelper;
        $this->url = $url;
        $this->customerSession = $customerSession;
        $this->_actionFlag = $actionFlag;
        $this->configHelper = $configHelper;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @inheritdoc
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $front = $observer->getEvent()->getControllerAction();

        if ($front instanceof \Magento\Framework\App\Action\AbstractAction) {
            $request = $front->getRequest();
            $response = $front->getResponse();
        } else {
            $request = $this->request;
            $response = $this->response;
        }

        if ($this->customerSession->getPrivatesaleViewPage()
            && $this->customerSession->getCustomerGroupId()
            && ! $request->isAjax()
            && ! $this->splashpage->isEnabledLaunchingSoon()
        ) {
            $url = $this->customerSession->getPrivatesaleViewPage();
            $this->customerSession->setPrivatesaleViewPage(null);
            $response->setRedirect($url)->sendResponse();
            $this->_actionFlag->set('', ActionInterface::FLAG_NO_DISPATCH, true);
            return;
        }

        if (! $this->configHelper->isModuleEnabled()
            || $this->previewHelper->isAllow()
            || ($this->customerSession->getCustomerGroupId() && ! $this->splashpage->isEnabledLaunchingSoon())
        ) {
            return;
        }

        if ($this->_canRedirect($request)) {
            if ($this->splashpage->isEnabledRedirect()) {
                $redirectUrl = $this->splashpage->getLandingPageUrl();

                if ((int) $this->splashpage->getData('landing_page') === SplashPage::MAGENTO_LOGIN_PAGE) {
                    $this->messageManager->addNoticeMessage(
                        __(
                            'Please note that you need to be authenticated user to' .
                            ' browse this website. Please log in or register to continue.'
                        )
                    );
                }

                if (! $this->customerSession->getPrivatesaleViewPage()
                    && ! $request->isAjax()
                    && strpos($this->url->getCurrentUrl(), 'logout') === false
                ) {
                    $this->customerSession->setPrivatesaleViewPage($this->url->getCurrentUrl());
                }

                $response->setRedirect($redirectUrl)->sendResponse();
                $this->_actionFlag->set('', ActionInterface::FLAG_NO_DISPATCH, true);
            }
        }
    }

    /**
     * Can redirect
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return boolean
     */
    protected function _canRedirect(RequestInterface $request): bool
    {
        $module = $request->getRouteName();
        $controller = $request->getControllerName();
        $action = strtolower($request->getActionName() ?? "");

        if ($module === 'pslogin') {
            return false;
        }

        if ($module === 'customer' && $controller === 'ajax') {
            return false;
        }

        if ($module === 'customer'
            && $controller === 'account'
            && in_array($action, ['index', 'login', 'create', 'forgotpassword', 'confirm'])
        ) {
            return false;
        }

        if ($module === 'customer'
            && $controller === 'account'
            && $this->splashpage->isEnabledRedirect()
        ) {
            return true;
        }

        return ($module !== 'prprivatesale' && $controller !== 'splashpage')
            && ($module !== 'contacts')
            && ($module !== 'cms' || ($module === 'cms' && $controller === 'index'))
            && ($module !== 'contact')
            && ($module !== 'prcr' && $controller !== 'timer');
    }
}
