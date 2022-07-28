<?php


namespace Magenest\AbandonedCart\Controller\Adminhtml\Rule;

use Magento\Framework\App\ResponseInterface;

class Duplicate extends \Magenest\AbandonedCart\Controller\Adminhtml\Rule
{


    /**
     * @inheritDoc
     */
    public function execute()
    {
        try {
            $collection = $this->_filer->getCollection($this->_collectionFactory->create());
            $count = 0;
            foreach ($collection as $rule) {
                $dataDuplicate = $rule->getData();
                $countDuplicate = intval($rule->getData('duplicate'))  + 1;
                $rule->setData('duplicate', $countDuplicate);
                $this->_resourceRule->save($rule);
                unset($dataDuplicate['id']);
                $dataDuplicate['status'] = '0';
                $dataDuplicate['duplicate'] = 0;
                $dataDuplicate['name'] = $dataDuplicate['name'].'_duplicate_'.$countDuplicate;
                $rule->setData($dataDuplicate);
                $this->_resourceRule->save($rule);
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
