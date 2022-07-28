<?php
namespace Magenest\FbChatbot\Controller\Adminhtml\Message;

use Magento\Framework\Exception\NoSuchEntityException;

class Edit extends AbstractMessage
{
    /**
     * Initialize current author and set it in the registry.
     *
     * @return int
     */
    protected function _initAuthor()
    {
        return $this->getRequest()->getParam('id');
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function execute()
    {
        $messageId = $this->_initAuthor();
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $redirectPage = $this->resultRedirectFactory->create();
        if ($messageId === null) {
            $resultPage->addBreadcrumb(__('New Button'), __('New Message'));
            $resultPage->getConfig()->getTitle()->prepend(__('New Message'));
        } else {
            $resultPage->addBreadcrumb(__('Edit Message'), __('Edit Message'));
            try {
                $resultPage->getConfig()->getTitle()->prepend(
                    $this->messageRepository->getById($messageId)->getName()
                );
            }catch (NoSuchEntityException $eNoSuchEntity){
                $redirectPage->setPath('*/*/index');
                $this->messageManager->addErrorMessage(__("The message that was requested doesn't exist. Verify the message and try again."));
                return $redirectPage;
            }catch (\Exception $e){
                $redirectPage->setPath('*/*/index');
                $this->messageManager->addExceptionMessage($e, $e->getMessage());
                return $redirectPage;
            }
        }
        return $resultPage;
    }
}
