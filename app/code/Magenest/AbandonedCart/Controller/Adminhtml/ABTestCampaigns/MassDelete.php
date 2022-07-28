<?php

namespace Magenest\AbandonedCart\Controller\Adminhtml\ABTestCampaigns;

use Magenest\AbandonedCart\Model\ResourceModel\Rule;
use Magento\Ui\Component\MassAction\Filter;

class MassDelete extends \Magenest\AbandonedCart\Controller\Adminhtml\ABTestCampaigns
{

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $collection = $this->_filer->getCollection($this->_campaignCollectionFactory->create());
            $count      = 0;
            $campaignIds    = [];
            foreach ($collection->getItems() as $item) {
                $campaignIds[] = $item->getId();
                $count++;
            }
            /** @var \Magenest\AbandonedCart\Model\ABTestCampaignFactory $_aBTestCampaignFactory */
            $ruleModel = $this->_aBTestCampaignFactory->create();
            $ruleModel->deleteMultiple($campaignIds);
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been deleted.', $count)
            );
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            $this->_logger->critical($exception->getMessage());
        }
        return $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }
}
