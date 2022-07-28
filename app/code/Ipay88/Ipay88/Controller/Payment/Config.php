<?php
/**
 * @author iPay88 Inc. <support@ipay88.com.my>
 * @package iPay88\ThirdpartyIntegration\Magento
 * @Description: Using for Magento 2.x and Philippines Only.
 */
namespace IPay88\IPay88\Controller\Payment;

use Magento\Checkout\Model\Session;

class Config extends \Magento\Framework\App\Action\Action
{
    protected $resultJsonFactory;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Registry $registry
    )
    {

        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
    }

    private function getInitConfig()
    {
        if (!class_exists('Ipay88_Config')) {
            $config = \Magento\Framework\App\Filesystem\DirectoryList::getDefaultConfig();
            require_once(BP . '/' . $config['lib_internal']['path'] . "/ipay88-php/Include.php");
        }
    }

    public function execute()
    {
        /* @var  $helper \Ipay88\Ipay88\Helper\Data */
        $helper = $this->_objectManager->create('Ipay88\Ipay88\Helper\Data');
        $widget = $helper->getWiget();

        return $this->resultJsonFactory->create()->setData([
            'wiget' => $widget
        ]);
    }

    /**
     * Return checkout session object
     *
     * @return Session
     */
    protected function _getCheckoutSession()
    {
        return $this->_checkoutSession;
    }
}