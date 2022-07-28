<?php
namespace Acommerce\Gtm\Observer;

use Magento\Framework\Event\ObserverInterface;

class CoreLayoutRenderElementObserver implements ObserverInterface
{
    /**
     * @var \Acommerce\Gtm\Helper\Data
     */
    protected $helper;

    /**
     * @param \Acommerce\Gtm\Helper\Data $helper
     */
    public function __construct(\Acommerce\Gtm\Helper\Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return self
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->helper->isEnabled()) {
            return $this;
        }

        $moduleName = $this->helper->getRequest()->getModuleName();
        $controllerName = $this->helper->getRequest()->getControllerName();

        $elementName = $observer->getData('element_name');

        if($moduleName == 'catalogsearch') {
           return $this;
        }

        if($moduleName == 'catalog' && $controllerName == 'category') {
            return $this;
        }

        if ($elementName != 'weltpixel_gtm_footer') {
            return $this;
        }

        $transport = $observer->getData('transport');
        $html = $transport->getOutput();

        $scriptContent = $this->helper->getDataLayerScript();
        $html = $html . PHP_EOL . $scriptContent;

        $transport->setOutput($html);

        return $this;
    }
}