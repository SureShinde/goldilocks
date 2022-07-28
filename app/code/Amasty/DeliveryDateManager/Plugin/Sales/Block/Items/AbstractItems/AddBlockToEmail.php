<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Plugin\Sales\Block\Items\AbstractItems;

use Amasty\DeliveryDateManager\Model\Config\Source\IncludeInto;
use Amasty\DeliveryDateManager\Model\ConfigProvider;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\Get;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class AddBlockToEmail
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Get
     */
    private $getDeliveryOrder;

    public function __construct(
        ConfigProvider $configProvider,
        Get $getDeliveryOrder,
        LoggerInterface $logger
    ) {
        $this->configProvider = $configProvider;
        $this->getDeliveryOrder = $getDeliveryOrder;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Sales\Block\Items\AbstractItems $subject
     * @param string $result HTML
     *
     * @return string
     */
    public function afterToHtml(\Magento\Sales\Block\Items\AbstractItems $subject, string $result): string
    {
        $order = $subject->getOrder();
        if (!$order || !$order->getId()) {
            return $result;
        }

        if (!$this->configProvider->isEnabled($order->getStoreId())) {
            return $result;
        }

        $place = $this->resolvePlace($subject);

        if (!$place) {
            return $result;
        }

        try {
            $deliveryDate = $this->getDeliveryOrder->getByOrderId((int)$order->getId());
        } catch (NoSuchEntityException $e) {
            return $result;
        }

        try {
            $addToResult = $subject->getLayout()
                ->createBlock(
                    \Amasty\DeliveryDateManager\Block\Sales\Order\Email\Deliverydate::class,
                    'deliverydate_info',
                    [
                        'data' => [
                            'order' => $order,
                            'delivery_date' => $deliveryDate,
                            'place' => $place
                        ]
                    ]
                )
                ->toHtml();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->error($e->getLogMessage());
            $addToResult = '';
        }

        return $addToResult . $result;
    }

    /**
     * @param \Magento\Sales\Block\Items\AbstractItems $subject
     * @return string
     */
    private function resolvePlace(\Magento\Sales\Block\Items\AbstractItems $subject): string
    {
        $place = '';
        if ($subject instanceof \Magento\Sales\Block\Order\Email\Invoice\Items) {
            $place = IncludeInto::INVOICE_EMAIL;
        } elseif ($subject instanceof \Magento\Sales\Block\Order\Email\Shipment\Items) {
            $place = IncludeInto::SHIPMENT_EMAIL;
        } elseif ($subject instanceof \Magento\Sales\Block\Order\Email\Items) {
            $place = IncludeInto::ORDER_EMAIL;
        }

        return $place;
    }
}
