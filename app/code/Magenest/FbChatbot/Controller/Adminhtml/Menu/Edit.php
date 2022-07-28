<?php
namespace Magenest\FbChatbot\Controller\Adminhtml\Menu;

use Magento\Framework\Exception\NoSuchEntityException;

class Edit extends AbstractMenu
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
     * @throws \Exception
     */
    public function execute()
    {
        $menuId = $this->_initAuthor();
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($menuId === null) {
            $resultPage->addBreadcrumb(__('New Button'), __('New Menu'));
            $resultPage->getConfig()->getTitle()->prepend(__('New Menu'));
        } else {
            $resultPage->addBreadcrumb(__('Edit Message'), __('Edit Menu'));
            try {
                $resultPage->getConfig()->getTitle()->prepend(
                    $this->menuRepository->getById($menuId)->getName()
                );
            }catch (NoSuchEntityException $noSuchEntityException){
                $resultRedirect->setPath('*/*/index');
                $this->messageManager->addErrorMessage(__("The message that was requested doesn't exist. Verify the message and try again."));
                return $resultRedirect;
            }catch (\Exception $e){
                $resultRedirect->setPath('*/*/index');
                $this->messageManager->addExceptionMessage($e, $e->getMessage());
                return $resultRedirect;
            }
        }
        return $resultPage;
    }
}
