<?php
namespace Magenest\FbChatbot\Controller\Adminhtml\Menu;

use Magento\Framework\Exception\NoSuchEntityException;

class Save extends AbstractMenu
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($duplicateId = $this->getRequest()->getParam($this->_idField)){
            try {
                $menu = $this->menuRepository->getById($duplicateId);
                $data = $menu->getData();
                unset($data[$this->_entityId]);
                $model = $this->menuFactory->create();
                $model->setData($data);
                $menuDup = $this->menuRepository->save($model);
                $this->messageManager->addSuccessMessage(__('Item was successfully duplicated.'));
                $this->bot->setupPersistentMenu();
                $resultRedirect->setPath('*/*/edit', ['id' => $menuDup->getId()]);
            } catch (NoSuchEntityException $noSuchEntityException){
                $this->messageManager->addErrorMessage(__("Item that was requested doesn't exist. Verify item and try again."));
                $resultRedirect->setPath('*/*/index');
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Unable to proceed. Please, try again.'));
                $resultRedirect->setPath('*/*/index');
            }
            return $resultRedirect;
        }else if ($data = $this->getRequest()->getParams()){
            try {
                $menu = $this->menuFactory->create();
                if(empty($data[$this->_entityId])){
                    unset($data[$this->_entityId]);
                }else{
                    $menu = $this->menuRepository->getById($data[$this->_entityId]);
                }
                $menu->setData($data);
                $this->menuRepository->save($menu);
                $this->messageManager->addSuccessMessage(__('Item was successfully saved'));
                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath('*/*/edit', ['id' => $menu->getId()]);
                } else {
                    $resultRedirect->setPath('*/*/index');
                }
                $this->bot->setupPersistentMenu();
            }catch (NoSuchEntityException $noSuchEntityException) {
                $this->messageManager->addErrorMessage(__("Item that was requested doesn't exist. Verify item and try again."));
                $resultRedirect->setPath('*/*/index');
            }catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, $e->getMessage());
            }
            return $resultRedirect;
        }else {
            $this->messageManager->addErrorMessage(__('Unable to find item to save'));
            return $resultRedirect->setPath('*/*/');
        }
    }
}
