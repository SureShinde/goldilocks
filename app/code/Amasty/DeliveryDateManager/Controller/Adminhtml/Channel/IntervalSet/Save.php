<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Controller\Adminhtml\Channel\IntervalSet;

use Amasty\DeliveryDateManager\Model\TimeInterval\Set\Get;
use Amasty\DeliveryDateManager\Model\TimeInterval\Set\Save as SaveTimeIntervalSet;
use Amasty\DeliveryDateManager\Ui\Component\Form\Channel\Modal\TimeIntervalSetDataProvider;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Psr\Log\LoggerInterface;

class Save extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_DeliveryDateManager::channel_intervals';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Get
     */
    private $timeSetGetter;

    /**
     * @var SaveTimeIntervalSet
     */
    private $timeIntervalSetSaver;

    /**
     * @var Save\TimeIntervalResolver
     */
    private $saveTimeIntervalResolver;

    public function __construct(
        Context $context,
        LoggerInterface $logger,
        Get $timeSetGetter,
        SaveTimeIntervalSet $timeIntervalSetSaver,
        Save\TimeIntervalResolver $saveTimeIntervalResolver
    ) {
        $this->logger = $logger;
        $this->timeSetGetter = $timeSetGetter;
        $this->timeIntervalSetSaver = $timeIntervalSetSaver;
        $this->saveTimeIntervalResolver = $saveTimeIntervalResolver;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            return $this->resultFactory->create(ResultFactory::TYPE_JSON);
        }

        try {
            $timeIds = [];
            $timeIntervalSetData = $this->getRequest()->getParams();

            if (!empty($timeIntervalSetData[TimeIntervalSetDataProvider::ROWS_KEY])) {
                $timeIds = $this->saveTimeIntervalResolver
                    ->execute($timeIntervalSetData[TimeIntervalSetDataProvider::ROWS_KEY]);
            }

            $timeSetId = $timeIntervalSetData['set_id'] ?? null;
            $timeSet = $this->timeSetGetter->execute((int)$timeSetId);
            $timeSet->setName($timeIntervalSetData['name']);
            $timeSet->setTimeIds($timeIds);

            $this->timeIntervalSetSaver->execute($timeSet);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }

        $closeAction = true;
        if ($this->getRequest()->getParam('back', false)) {
            $closeAction = false;
        }

        return $this->resultFactory
            ->create(ResultFactory::TYPE_JSON)
            ->setData(
                [
                    'closeAction' => $closeAction,
                    'status' => 'done',
                    'id' => $timeSet->getId()
                ]
            );
    }
}
