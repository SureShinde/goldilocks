<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Cron;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateOrderInterface;
use Amasty\DeliveryDateManager\Api\Data\DeliveryDateOrderSearchResultInterface;
use Amasty\DeliveryDateManager\Model\ConfigProvider;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\GetList as DeliveryOrderGetList;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\OutputFormatter;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\Save as DeliveryOrderSaver;
use Amasty\DeliveryDateManager\Model\EmailSender;
use Amasty\DeliveryDateManager\Model\TimeInterval\MinsToTimeConverter;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Store\Model\ScopeInterface;

class Reminder
{
    // 60 min. * 60 sec. = 3600 sec.
    public const SEC_IN_HOUR = 3600;

    public const REMINDER_TEMPLATE_ID = 'amdeliverydate_reminder_email_template';

    public const ALLOWED_ORDER_STATUSES = [
        'pending',
        'processing',
        'pending_payment',
        'pending_paypal'
    ];

    /**
     * @var DeliveryOrderGetList
     */
    private $deliveryOrderGetList;

    /**
     * @var SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * @var DeliveryOrderSaver
     */
    private $deliveryOrderSaver;

    /**
     * @var OrderCollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var EmailSender
     */
    private $emailSender;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var MinsToTimeConverter
     */
    private $minsToTimeConverter;

    /**
     * @var OutputFormatter
     */
    private $outputFormatter;

    public function __construct(
        DeliveryOrderGetList $deliveryOrderGetList,
        SearchCriteriaBuilder $criteriaBuilder,
        DeliveryOrderSaver $deliveryOrderSaver,
        OrderCollectionFactory $orderCollectionFactory,
        EmailSender $emailSender,
        ConfigProvider $configProvider,
        TimezoneInterface $timezone,
        MinsToTimeConverter $minsToTimeConverter,
        OutputFormatter $outputFormatter
    ) {
        $this->deliveryOrderGetList = $deliveryOrderGetList;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->deliveryOrderSaver = $deliveryOrderSaver;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->emailSender = $emailSender;
        $this->configProvider = $configProvider;
        $this->timezone = $timezone;
        $this->minsToTimeConverter = $minsToTimeConverter;
        $this->outputFormatter = $outputFormatter;
    }

    public function execute(): void
    {
        if (!$this->configProvider->isEnabled() || !$this->configProvider->isReminderEnabled()) {
            return;
        }

        $deliveryOrderItems = $this->getDeliveryOrderItems();
        $orderIds = $this->getOrderIds($deliveryOrderItems);
        if (empty($orderIds)) {
            return;
        }

        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToFilter(OrderInterface::ENTITY_ID, ['in' => $orderIds]);
        foreach ($deliveryOrderItems->getItems() as $deliveryDate) {
            /** @var Order $order */
            $order = $orderCollection->getItemById($deliveryDate->getOrderId());
            if (!in_array($order->getStatus(), self::ALLOWED_ORDER_STATUSES, true)) {
                continue;
            }

            $storeId = (int)$order->getStoreId();
            $recipients = explode(',', $this->configProvider->getReminderRecipient($storeId));
            if (empty($recipients)) {
                continue;
            }

            $now = $this->timezone->scopeDate($storeId, null, true)->getTimestamp();
            $sendEmailTime = $this->calcSendEmailTime($storeId, $deliveryDate->getDate(), $deliveryDate->getTimeFrom());
            if ($now >= $sendEmailTime) {
                $vars['delivery'] = $this->formatOutputFields($deliveryDate);
                $vars['order'] = $order;
                $template = $this->configProvider->getReminderTemplate($storeId) ?: self::REMINDER_TEMPLATE_ID;
                $sendFrom = $this->configProvider->getReminderSender($storeId);

                $this->emailSender->execute($recipients, $storeId, $vars, $template, $sendFrom);

                $deliveryDate->setReminder(DeliveryDateOrderInterface::REMINDER_SENT_VALUE);
                $this->deliveryOrderSaver->execute($deliveryDate);
            }
        }
    }

    /**
     * @return DeliveryDateOrderSearchResultInterface
     */
    private function getDeliveryOrderItems(): DeliveryDateOrderSearchResultInterface
    {
        /** @var SearchCriteriaBuilder $criteriaBuilder */
        $criteriaBuilder = $this->criteriaBuilder
            ->addFilter(DeliveryDateOrderInterface::REMINDER, 0)
            ->addFilter(DeliveryDateOrderInterface::DATE, true, 'notnull')
            ->create();

        return $this->deliveryOrderGetList->execute($criteriaBuilder);
    }

    /**
     * @param DeliveryDateOrderSearchResultInterface $deliveryOrderItems
     * @return array
     */
    private function getOrderIds(DeliveryDateOrderSearchResultInterface $deliveryOrderItems): array
    {
        $orderIds = [];
        foreach ($deliveryOrderItems->getItems() as $item) {
            $orderIds[] = $item->getOrderId();
        }

        return $orderIds;
    }

    /**
     * @param int $storeId
     * @param string|null $date
     * @param int|null $timeFrom
     * @return int
     */
    private function calcSendEmailTime(int $storeId, ?string $date, ?int $timeFrom): int
    {
        $date = $date ?: 'today';
        $timeFrom = $timeFrom ? $this->minsToTimeConverter->toSystemTime($timeFrom) : '';
        $timezone = $this->timezone->getConfigTimezone(ScopeInterface::SCOPE_STORE, $storeId);
        $sendOrderTimestamp = $this->timezone->scopeDate($storeId, $date . ' ' . $timeFrom . ' ' . $timezone, true)
            ->getTimestamp();
        $timeBeforeSendOrder = self::SEC_IN_HOUR * $this->configProvider->getReminderTimeBefore($storeId);

        return $sendOrderTimestamp - $timeBeforeSendOrder;
    }

    /**
     * @param DeliveryDateOrderInterface $deliveryDate
     * @return DeliveryDateOrderInterface
     */
    private function formatOutputFields(DeliveryDateOrderInterface $deliveryDate): DeliveryDateOrderInterface
    {
        $formattedDate = $this->outputFormatter->getFormattedDateFromDeliveryOrder($deliveryDate);
        $deliveryDate->setDate($formattedDate);

        return $this->outputFormatter->formatOutputTimes($deliveryDate);
    }
}
