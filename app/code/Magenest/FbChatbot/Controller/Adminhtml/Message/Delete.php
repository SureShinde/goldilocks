<?php
namespace Magenest\FbChatbot\Controller\Adminhtml\Message;

use Magento\Framework\Exception\NoSuchEntityException;

class Delete extends AbstractMessage
{
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if($this->canDelete()){
            try {
                $menuId = $this->messageRepository->getById($this->getRequest()->getParam($this->_idField))->getId();
                $this->messageRepository->deleteById($menuId);
                $this->messageManager->addSuccess(__('Item was successfully deleted.'));
                $resultRedirect->setPath('*/*/index');
            }catch (NoSuchEntityException $noSuchEntityException){
                $this->messageManager->addExceptionMessage($noSuchEntityException, __("Item that was requested doesn't exist. Verify item and try again."));
                $resultRedirect->setPath('*/*/index');
            }catch (\Exception $e){
                $this->messageManager->addExceptionMessage($e, __('Unable to proceed. Please, try again.'));
                $resultRedirect->setPath('*/*/index');
            }
            return $resultRedirect;
        }else{
            $this->messageManager->addWarningMessage(__('Cannot delete item with id %1', $this->getRequest()->getParam($this->_idField)));
            return $resultRedirect->setPath('*/*/index');
        }
    }
}
