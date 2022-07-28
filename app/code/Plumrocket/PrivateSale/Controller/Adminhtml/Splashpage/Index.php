<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket Private Sales and Flash Sales
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Controller\Adminhtml\Splashpage;

use Magento\Framework\Controller\ResultFactory;
use Plumrocket\PrivateSale\Controller\Adminhtml\Splashpage;

class Index extends Splashpage
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $this->splashpage->setAdmin(true);

        if ($this->getRequest()->getParam('store') != null) {
            $this->splashpage->setStoreId($this->getRequest()->getParam('store'));
        }

        $this->_addBreadcrumb(__('Manage Splash Page'), __('Manage Splash Page'));
        $result->getConfig()->getTitle()->prepend(__('Splash Page'));

        return $result;
    }
}
