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

namespace Plumrocket\PrivateSale\Controller\Adminhtml\FlashSale;

use Magento\Backend\App\Action;
use Magento\Framework\File\Mime;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\File\Csv;
use Plumrocket\PrivateSale\Model\Import\FlashSale;

class Import extends Action
{
    /**
     * @var Csv
     */
    protected $csvHandler;

    /**
     * @var Mime
     */
    protected $mime;

    /**
     * @var FlashSale
     */
    private $importModel;

    /**
     * Import constructor.
     *
     * @param Action\Context $context
     * @param Csv $csvHandler
     * @param FlashSale $importModel
     * @param Mime $mime
     */
    public function __construct(
        Action\Context $context,
        Csv $csvHandler,
        FlashSale $importModel,
        Mime $mime
    ) {
        parent::__construct($context);
        $this->csvHandler = $csvHandler;
        $this->importModel = $importModel;
        $this->mime = $mime;
    }

    /**
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        try {
            $fileName = $this->getRequest()->getFiles()['file']['tmp_name'] ?? null;
            $mimeType = $this->mime->getMimeType($fileName);

            if (! ('text/csv' === $mimeType || 'text/plain' === $mimeType)) {
                return $this->invalidFileMessage();
            }
        } catch (\InvalidArgumentException $e) {
            return $this->invalidFileMessage();
        }

        return parent::dispatch($request);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        try {
            $request = $this->getRequest();
            $fileName = $request->getFiles()['file']['tmp_name'] ?? null;
            $eventId = (int) $request->getParam('event_id');
            $fieldSeparator = $request->getParam('field_separator');
            $validationRule = $request->getParam('validation');
            $this->csvHandler->setDelimiter($fieldSeparator);
            $data = $this->csvHandler->getData($fileName);
            $this->importModel->setValidationRule($validationRule);
            $this->importModel->importData($data, $eventId);
        } catch (\Exception $e) {
            $result->setHttpResponseCode(400)->setData(['message' => $e->getMessage()]);
        }

        return $result;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    private function invalidFileMessage()
    {
        $this->_actionFlag->set('', 'no-dispatch', true);
        return $this->resultFactory
            ->create(ResultFactory::TYPE_JSON)
            ->setHttpResponseCode(400)
            ->setData(['message' => __('Invalid file')]);
    }
}
