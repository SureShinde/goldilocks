<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Controller\Adminhtml\Channel;

use Amasty\DeliveryDateManager\Controller\Adminhtml\Channel;
use Magento\Framework\App\Action\HttpGetActionInterface;

class Index extends Channel implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_DeliveryDateManager::deliverydate_channel_grid';

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('Delivery Configuration'));

        return $resultPage;
    }
}
