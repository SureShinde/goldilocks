<?php

namespace Magenest\AbandonedCart\Controller\Adminhtml\ABTestCampaigns;

use Magenest\AbandonedCart\Helper\Data;

class Save extends \Magenest\AbandonedCart\Controller\Adminhtml\ABTestCampaigns
{

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $params = $this->_request->getParams();
        $redirectBack = $this->_request->getParam('back', false);
        try {
            /** @var \Magenest\AbandonedCart\Model\Rule $ruleModel */
            $aBTestCampaignModel = $this->_aBTestCampaignFactory->create();
            if ($id = $this->_request->getParam('id')) {
                $data['id'] = $id;
            }
            //check data before save
            $data['status'] = isset($params['status']) ? $params['status'] : '';
            $data['name'] = isset($params['name']) ? $params['name'] : '';
            $data['description'] = isset($params['description']) ? $params['description'] : '';
            $data['from_date'] = isset($params['from_date']) ? $params['from_date'] : '';
            $data['to_date'] = isset($params['to_date']) ? $params['to_date'] : '';

            //validate date
            if ($data['from_date'] != "" && $this->filter($data['from_date'])) {
                throw new \Exception($this->filter($data['from_date']));
            }
            if ($data['to_date'] != "" && $this->filter($data['to_date'])) {
                throw new \Exception($this->filter($data['to_date']));
            }

            if ($this->validDateFromTo($data['from_date'], $data['to_date'])) {
                throw new \Exception($this->validDateFromTo($data['from_date'], $data['to_date']));
            }

            //save data
            $aBTestCampaignModel->setData($data);
            $this->_abTestCampaignResource->save($aBTestCampaignModel);
            $this->messageManager->addSuccessMessage(__('You saved the A/B Test Campaign.'));
            if ($redirectBack === 'edit') {
                $resultRedirect->setPath(
                    '*/*/edit',
                    ['id' => $aBTestCampaignModel->getId(), 'back' => null, '_current' => true]
                );
            } else {
                $resultRedirect->setPath('*/*/index');
            }

        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
            $this->_logger->critical($e->getMessage());
            if ($redirectBack === 'edit' && isset($data['id'])) {
                $resultRedirect->setPath(
                    '*/*/edit',
                    ['id' => $data['id'], 'back' => null, '_current' => true]
                );
            } else {
                $resultRedirect->setPath('*/*/addNew');
            }
        }
        return $resultRedirect;
    }

    /**
     * @param $from
     * @param $to
     * @return bool|\Magento\Framework\Phrase
     */
    public function validDateFromTo($from, $to)
    {
        if ($from == '' || $to == '') {
            return false;
        } else {
            $timestampFrom = $this->_dateTime->timestamp($from);
            $timestampTo = $this->_dateTime->timestamp($to);
            if ($timestampFrom > $timestampTo) {
                return __('From Date must not be later than To Date');
            } else {
                return false;
            }
        }
    }


    /**
     * @param $value
     * @return bool|\Magento\Framework\Phrase
     */
    public function filter($value)
    {
        $time = strtotime($value);
        if (!$time) {
            return __("Invalid input datetime format of value '$value'");
        } else {
            return false;
        }
    }
}
