<?php
namespace Magenest\FbChatbot\Controller\Adminhtml\Message;

class Index extends AbstractMessage {
    /**
     * @inheritDoc
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magenest_FbChatbot::message');
        $resultPage->getConfig()->getTitle()->prepend(__('Messages'));

        return $resultPage;
    }
}
