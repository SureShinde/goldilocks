<?php

namespace Magenest\AbandonedCart\Controller\Adminhtml\Blacklist;

use Magenest\AbandonedCart\Model\ResourceModel\BlackList\CollectionFactory as BlackListCollection;

class Save extends \Magenest\AbandonedCart\Controller\Adminhtml\Blacklist
{
    /** @var \Magento\Framework\File\Csv  */
    protected $csv;

    /** @var BlackListCollection */
    protected $_collection;

    protected $logError = [];

    public function __construct(
        \Magento\Framework\File\Csv $csv,
        \Magenest\AbandonedCart\Model\BlackListFactory $blacklistFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Backend\App\Action\Context $context,
        BlackListCollection $collection
    ) {
        $this->csv = $csv;
        $this->_collection        = $collection;
        parent::__construct($blacklistFactory, $logger, $registry, $pageFactory, $context);
    }

    public function execute()
    {
        try {
            $count = 0;
            $file = $this->getRequest()->getFiles();
            if (isset($file)) {
                $blacklist = $file->getArrayCopy();
                if (is_array($blacklist) && isset($blacklist['blacklist'])) {
                    $count = $this->import($blacklist['blacklist']);
                }
            }
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been inserted.', $count)
            );
            if (!empty($this->logError['blank_line'])) {
                $this->messageManager->addWarningMessage('The line is blank in row: '.implode(",", $this->logError['blank_line']));
            }
            if (!empty($this->logError['valid_email'])) {
                $this->messageManager->addWarningMessage('Wrong email format in row: '.implode(",", $this->logError['valid_email']));
            }
            if (!empty($this->logError['exist_email'])) {
                $this->messageManager->addWarningMessage('Email already exists in row: '.implode(",", $this->logError['exist_email']));
            }
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }
        return $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }

    /**
     * @param $file
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function import($file)
    {
        if (!isset($file['tmp_name']) || $file['tmp_name'] == "") {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file upload attempt.'));
        }
        if ($file['type'] != 'text/csv') {
            throw new \Magento\Framework\Exception\LocalizedException(__('You must upload file csv.'));
        }
        $csvData = $this->csv->getData($file['tmp_name']);
        $count = 0;
        $data = 1;
        $records = [];
        $columns = $csvData[0];
        $emailArray = [];
        foreach ($columns as $key => $value) {
            if ($value == 'address') {
                $data = $key;
            }
        }
        foreach ($csvData as $key => $csv) {
            $email = $csvData[$key][$data];
            if ($count == 0) {
                $count++;
                continue;
            }
            if ($this->checkEmailImport($key, $email)) {
                if (!in_array($email, $emailArray)) {
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $records[] = [
                            'address' => $email
                        ];
                        $emailArray[] = $email;
                        $count++;
                    } else {
                        $this->logError['valid_email'][] = $key+1;
                    }
                } else {
                    $this->logError['exist_email'][] = $key+1;
                }
            }
        }
        /** @var \Magenest\AbandonedCart\Model\BlackList $blacklistModel */
        $blacklistModel = $this->_blacklistFactory->create();
        if (!empty($records)) {
            $blacklistModel->insertMultiple($records);
        }
        return $count > 0 ? ($count - 1) : 0;
    }

    /**
     * @param $email
     * @param $row
     * @return bool
     */
    public function checkEmailImport($row, $email)
    {
        if ($email) {
            $collection = $this->_collection->create()->addFieldToFilter('address', $email);
            if ($collection->count()) {
                $this->logError['exist_email'][] = $row+1;
            } else {
                return true;
            }
        } else {
            $this->logError['blank_line'][] = $row+1;
        }
        return false;
    }
}
