<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Controller\Adminhtml\Channel\Schedule;

use Amasty\DeliveryDateManager\Model\DateSchedule\DateScheduleData;
use Amasty\DeliveryDateManager\Model\DateSchedule\DateScheduleDataFactory;
use Amasty\DeliveryDateManager\Model\DateSchedule\Save as SaveDateSchedule;
use Amasty\DeliveryDateManager\Model\Preprocessor\CompositePreprocessor;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Psr\Log\LoggerInterface;

class Save extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_DeliveryDateManager::channel_schedules';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SaveDateSchedule
     */
    private $saveDateSchedule;

    /**
     * @var DateScheduleDataFactory
     */
    private $dateScheduleFactory;

    /**
     * @var CompositePreprocessor
     */
    private $dataPreprocessor;

    public function __construct(
        Context $context,
        LoggerInterface $logger,
        SaveDateSchedule $saveDateSchedule,
        DateScheduleDataFactory $dateScheduleFactory,
        CompositePreprocessor $dataPreprocessor
    ) {
        $this->logger = $logger;
        $this->saveDateSchedule = $saveDateSchedule;
        $this->dateScheduleFactory = $dateScheduleFactory;
        $this->dataPreprocessor = $dataPreprocessor;
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
            /** @var DateScheduleData $channelSchedule */
            $channelSchedule = $this->dateScheduleFactory->create();
            $channelScheduleData = $this->getRequest()->getParams();

            $this->dataPreprocessor->process($channelScheduleData);
            $channelSchedule->setData($channelScheduleData);
            $this->saveDateSchedule->execute($channelSchedule);
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
                    'id' => $channelSchedule->getScheduleId()
                ]
            );
    }
}
