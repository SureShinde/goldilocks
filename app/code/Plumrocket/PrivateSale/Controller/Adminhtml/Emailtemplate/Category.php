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
 * @package     Plumrocket Private Sales and Flash Sales v4.x.x
 * @copyright   Copyright (c) 2016 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\PrivateSale\Controller\Adminhtml\Emailtemplate;

use Magento\Framework\Controller\ResultFactory;
use Plumrocket\PrivateSale\Controller\Adminhtml\Emailtemplate;

class Category extends Emailtemplate
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $request = $this->getRequest();
        $storeId = $request->getParam('store_id');
        $date = $request->getParam('date');

        if (! $storeId) {
            $storeId = 0;
        }

        $eventOptions = [];

        if ($date) {
            /** @var \Plumrocket\PrivateSale\Model\Emailtemplate $emailTemplateModel */
            $emailTemplateModel = $this->_getModel();
            $events = $emailTemplateModel->loadEventsByCriteria($date, $storeId);
            $eventOptions = $emailTemplateModel->eventsToOptions($events);
        }

        return $result->setData([
            'count' => count($eventOptions),
            'categories' => $eventOptions,
            'select' => (string) $events->getSelect(),
        ]);
    }
}
