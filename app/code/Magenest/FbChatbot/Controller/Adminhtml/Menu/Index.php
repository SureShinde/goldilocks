<?php
namespace Magenest\FbChatbot\Controller\Adminhtml\Menu;

class Index extends AbstractMenu {
    /**
     * @inheritDoc
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magenest_FbChatbot::menu');
        $resultPage->getConfig()->getTitle()->prepend(__('Persistent Menu'));

        return $resultPage;
    }
}
