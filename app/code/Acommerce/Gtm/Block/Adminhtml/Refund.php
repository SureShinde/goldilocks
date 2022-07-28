<?php
namespace Acommerce\Gtm\Block\Adminhtml;

/**
 * Class \Acommerce\Gtm\Block\Adminhtml\Refund
 */
class Refund extends \Acommerce\Gtm\Block\Core
{

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendSession;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Acommerce\Gtm\Helper\Data          $helper
     * @param \Acommerce\Gtm\Model\Storage        $storage
     * @param \Magento\Framework\View\Page\Title               $pageTitle
     * @param \Magento\Customer\Model\Session                  $customerSession
     * @param \Magento\Backend\Model\Session                   $backendSession
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Acommerce\Gtm\Helper\Data $helper,
        \Acommerce\Gtm\Model\Storage $storage,
        \Magento\Framework\View\Page\Title $pageTitle,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Backend\Model\Session $backendSession,
        array $data = []
    ) {

        parent::__construct($context, $helper, $storage, $pageTitle, $customerSession, $data);
        $this->_backendSession = $backendSession;
    }

    /**
     * @return string
     */
    public function getDataLayerAsJson()
    {
        $this->_checkRefunds();
        return parent::getDataLayerAsJson();
    }


    private function _checkRefunds()
    {
        $refundsData = $this->_backendSession->getGtmrefunds();
        if ($refundsData) {
            $this->setEcommerceData('refund', $refundsData);
        }
        $this->_backendSession->unsetData('gtmrefunds');
    }
}