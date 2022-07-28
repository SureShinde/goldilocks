<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryOrder\Edit;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateOrderInterface;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\OutputFormatter;
use Amasty\DeliveryDateManager\Model\EditableConfigProvider;
use Amasty\DeliveryDateManager\Model\EmailSender;
use Magento\Framework\App\Area;
use Magento\Framework\Escaper;
use Magento\Sales\Api\Data\OrderInterface;

class NotificationSender
{
    public const ADMIN_RECIPIENT_KEY = 'admin';
    public const CUSTOMER_RECIPIENT_KEY = 'customer';

    /**
     * @var EditableConfigProvider
     */
    private $configProvider;

    /**
     * @var EmailSender
     */
    private $emailSender;

    /**
     * @var OutputFormatter
     */
    private $outputFormatter;

    /**
     * @var Escaper
     */
    private $escaper;

    public function __construct(
        EditableConfigProvider $configProvider,
        EmailSender $emailSender,
        OutputFormatter $outputFormatter,
        Escaper $escaper
    ) {
        $this->configProvider = $configProvider;
        $this->emailSender = $emailSender;
        $this->outputFormatter = $outputFormatter;
        $this->escaper = $escaper;
    }

    /**
     * Send Email notification to admin/customer
     *
     * @param DeliveryDateOrderInterface $deliveryDate
     * @param OrderInterface $order
     * @param string $recipient admin/customer
     */
    public function sendNotification(
        DeliveryDateOrderInterface $deliveryDate,
        OrderInterface $order,
        string $recipient
    ): void {
        $oldValues = $this->getFormattedOldValues($deliveryDate);
        if (!$oldValues) {
            return;
        }

        $storeId = (int)$order->getStoreId();

        if ($recipient === self::CUSTOMER_RECIPIENT_KEY) {
            $recipientEmails = $order->getCustomerEmail();
        } else {
            $recipientEmails = $this->configProvider->getAdminEmail($storeId);
        }

        if (empty($recipientEmails)) {
            return;
        }

        $senderId = $this->configProvider->getIdentity($storeId);
        $template = $this->configProvider->getEmailTemplate($storeId);

        $vars = [
            'delivery' => $deliveryDate,
            'order' => $order
        ];
        $vars = array_merge($vars, $oldValues);

        $this->emailSender->execute($recipientEmails, $storeId, $vars, $template, $senderId);
    }

    /**
     * @param DeliveryDateOrderInterface $deliveryDate
     * @return array
     */
    private function getFormattedOldValues(DeliveryDateOrderInterface $deliveryDate): array
    {
        $oldDeliveryDate = clone $deliveryDate;
        $oldDeliveryDate->setData($deliveryDate->getOrigData());

        $oldValues = [];
        if ($oldDeliveryDate->getDate() !== $deliveryDate->getDate()) {
            $oldValues['was_date'] = $this->outputFormatter->getFormattedDateFromDeliveryOrder($oldDeliveryDate);
        }

        if ($oldDeliveryDate->getTimeFrom() !== $deliveryDate->getTimeFrom()
            || $oldDeliveryDate->getTimeTo() !== $deliveryDate->getTimeTo()
        ) {
            $oldValues['was_time'] = $this->outputFormatter->getTimeLabelFromDeliveryOrder($oldDeliveryDate);
        }

        if ($oldDeliveryDate->getComment() !== $deliveryDate->getComment()) {
            $oldValues['was_comment'] = $this->escaper->escapeHtml(
                $this->outputFormatter->getComment($oldDeliveryDate),
                ['br']
            );
        }

        foreach ($oldValues as &$value) {
            if ($value === "") {
                $value = "-"; // avoid email template depend directive
            }
        }

        return $oldValues;
    }
}
