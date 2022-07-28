<?php

namespace Magenest\AbandonedCart\Controller\Adminhtml\Rule;

use Magenest\AbandonedCart\Controller\Adminhtml\Rule;
use Magenest\AbandonedCart\Helper\MandrillConnector;
use Magenest\AbandonedCart\Helper\SendMail;
use Magenest\AbandonedCart\Model\LogContent;
use Magenest\AbandonedCart\Model\TestCampaign;

class SendEmail extends Rule
{

    public function execute()
    {
        $params = $this->getRequest()->getParams();
        try {
            if (isset($params['id']) && $params['id']) {
                $id              = $params['id'];
                $logContentModel = $this->_logContentCollection->create()
                    ->addFieldToFilter('type', 'Campaign')
                    ->addFieldToFilter('id', $id)
                    ->getFirstItem();
                if ($params['type']) {
                    $emailTest = $this->_helperData->getConfig("abandonedcart/general/test_email");
                    /**  change recipient address email by address email test*/
                    $logContentModel->setData('recipient_adress', $emailTest);
                    $logContentModel->setData('send_test', true);
                }
                if ($this->_mandrillConnector->isEnable()) {
                    $this->_mandrillConnector->sendEmails($logContentModel);
                } else {
                    $this->_sendMailHelper->send($logContentModel);
                }
                $this->messageManager->addSuccessMessage(__('You sent message to "%1" successfully.', $logContentModel->getData('recipient_adress')));
            }
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            $this->_logger->critical($exception->getMessage());
        }
        return $this->resultRedirectFactory->create()->setPath(
            'abandonedcart/rule/edit',
            ['id' => $params['rule_id']]
        );
    }
}
