<?php

namespace Magenest\AbandonedCart\Controller\Adminhtml\ABTestCampaigns;

use Magento\Framework\Controller\ResultFactory;

class Edit extends \Magenest\AbandonedCart\Controller\Adminhtml\ABTestCampaigns
{
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $id = $this->getRequest()->getParam('id');

            /** @var \Magenest\AbandonedCart\Model\AbandonedCart $aBTestCampaignModel */
            $aBTestCampaignModel = $this->_aBTestCampaignFactory->create();


            if ($id) {
                $this->_abTestCampaignResource->load($aBTestCampaignModel, $id, 'id');
                if (!$aBTestCampaignModel->getId()) {
                    $this->messageManager->addErrorMessage(__('This A/B Test Campaign doesn\'t exist'));
                    $resultRedirect = $this->resultRedirectFactory->create();
                    return $resultRedirect->setPath('*/*/index');
                }
            }
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            $this->_logger->critical($exception->getMessage());
        }
        $this->_coreRegistry->register('abandonedcart_abtestcampaign', $aBTestCampaignModel);
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend($aBTestCampaignModel->getId() ? __($aBTestCampaignModel->getName()) : __('New A/B Test Campaign'));
        return $resultPage;
    }
}
