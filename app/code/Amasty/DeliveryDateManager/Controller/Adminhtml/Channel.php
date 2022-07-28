<?php

namespace Amasty\DeliveryDateManager\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

abstract class Channel extends Action
{
    public const ADMIN_RESOURCE = 'Amasty_DeliveryDateManager::deliverydate_channel_grid';

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    protected function initAction(): \Magento\Framework\Controller\ResultInterface
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_DeliveryDateManager::deliverydate_channel_grid');
        $resultPage->addBreadcrumb(__('Delivery Date: Configurations'), __('Delivery Date: Configurations'));
        $resultPage->getConfig()->getTitle()->prepend(__('Delivery Date: Configurations'));

        return $resultPage;
    }
}
