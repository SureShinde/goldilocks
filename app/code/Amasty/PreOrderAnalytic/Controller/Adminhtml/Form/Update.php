<?php

declare(strict_types=1);

namespace Amasty\PreOrderAnalytic\Controller\Adminhtml\Form;

use Amasty\PreOrderAnalytic\Model\AnalyticForm\GetAllData;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;

class Update extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_PreOrderAnalytic::report_board';

    /**
     * @var GetAllData
     */
    private $getAllData;

    public function __construct(GetAllData $getAllData, Context $context)
    {
        parent::__construct($context);
        $this->getAllData = $getAllData;
    }

    /**
     * @return Json
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($this->getAllData->execute());

        return $resultJson;
    }
}
