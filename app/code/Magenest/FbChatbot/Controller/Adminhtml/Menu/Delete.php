<?php
namespace Magenest\FbChatbot\Controller\Adminhtml\Menu;

use Magento\Framework\Exception\NoSuchEntityException;

class Delete extends AbstractMenu
{
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if($this->canDelete()){
            try {
                $menuId = $this->menuRepository->getById($this->getRequest()->getParam($this->_idField))->getId();
                $this->menuRepository->deleteById($menuId);
                $this->messageManager->addSuccess(__('Item was successfully deleted.'));
                $resultRedirect->setPath('*/*/index');
                $this->bot->setupPersistentMenu();
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
