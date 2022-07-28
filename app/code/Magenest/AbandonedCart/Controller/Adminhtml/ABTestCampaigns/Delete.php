<?php

namespace Magenest\AbandonedCart\Controller\Adminhtml\ABTestCampaigns;

class Delete extends \Magenest\AbandonedCart\Controller\Adminhtml\ABTestCampaigns
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        /** @var \Magenest\AbandonedCart\Model\AbandonedCart $aBTestCampaignModel */
        $aBTestCampaignModel = $this->_aBTestCampaignFactory->create();

        try {
            $this->_abTestCampaignResource->load($aBTestCampaignModel, $id, 'id')->delete($aBTestCampaignModel);
            $this->messageManager->addSuccessMessage(__('Your campaign has been deleted !'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error while trying to delete campaign: '));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/index', ['_current' => true]);
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/index', ['_current' => true]);
    }
}
