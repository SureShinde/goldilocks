<?php

namespace Magenest\AbandonedCart\Controller\Adminhtml\ABTestCampaigns;

use Magenest\AbandonedCart\Model\ABTestCampaign;

class ChangeStatus extends \Magenest\AbandonedCart\Controller\Adminhtml\ABTestCampaigns
{

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $collection = $this->_filer->getCollection($this->_campaignCollectionFactory->create());
            $count      = 0;
            foreach ($collection as $campaign) {
                $status = $campaign->getStatus();
                if ($status == 1) {
                    $campaign->setStatus(ABTestCampaign::CAMPAIGN_INACTIVE);
                } else {
                    $campaign->setStatus(ABTestCampaign::CAMPAIGN_ACTIVE);
                }
                $this->_abTestCampaignResource->save($campaign);
                $count++;
            }
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been changed.', $count)
            );
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            $this->_logger->critical($exception->getMessage());
        }
        return $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }
}
