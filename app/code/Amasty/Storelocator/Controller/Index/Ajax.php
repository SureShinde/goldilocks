<?php

namespace Amasty\Storelocator\Controller\Index;

class Ajax extends \Magento\Framework\App\Action\Action
{
    /**
     * Default customer account page
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();

        /** @var \Amasty\Storelocator\Block\Location $block */
        $block = $this->_view->getLayout()->getBlock('amlocator_ajax');

        $this->getResponse()->setBody($block->getJsonLocations());
    }
}
