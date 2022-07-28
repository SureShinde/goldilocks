<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Controller\Adminhtml\Channel;

use Amasty\DeliveryDateManager\Controller\Adminhtml\Channel;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class NewAction extends Channel implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_DeliveryDateManager::deliverydate_channel';

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        $resultForward->forward('edit');

        return $resultForward;
    }
}
