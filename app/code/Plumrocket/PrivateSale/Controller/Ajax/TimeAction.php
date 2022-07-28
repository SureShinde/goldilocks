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

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Plumrocket\PrivateSale\Model\CurrentDateTime;

class TimeAction extends Action
{
    /**
     * @var CurrentDateTime
     */
    private $currentDateTime;

    /**
     * TimeAction constructor.
     *
     * @param Context $context
     * @param CurrentDateTime $currentDateTime
     */
    public function __construct(
        Context $context,
        CurrentDateTime $currentDateTime
    ) {
        parent::__construct($context);
        $this->currentDateTime = $currentDateTime;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        if ($this->getRequest()->isXmlHttpRequest()) {
            $data = $this->getRequest()->getParam('data');
            $currentTimestamp = $this->currentDateTime->getGmtTimestamp();

            foreach ($data as $eventId => $time) {
                if ($time > $currentTimestamp) {
                    $data[$eventId] = $time - $currentTimestamp;
                } else {
                    $data[$eventId] = 0;
                }
            }

            $result->setData([
                'success' => true,
                'data' => $data
            ]);
        }

        return $result;
    }
}
