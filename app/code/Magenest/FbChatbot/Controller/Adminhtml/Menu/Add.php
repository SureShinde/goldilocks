<?php

namespace Magenest\FbChatbot\Controller\Adminhtml\Menu;

class Add extends AbstractMenu
{
    /**
     * forward to edit
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
