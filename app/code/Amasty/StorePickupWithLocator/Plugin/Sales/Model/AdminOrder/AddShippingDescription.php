<?php
declare(strict_types=1);

namespace Amasty\StorePickupWithLocator\Plugin\Sales\Model\AdminOrder;

use Amasty\Base\Model\MagentoVersion;
use Amasty\StorePickupWithLocator\Model\ConfigProvider;
use Magento\Sales\Model\AdminOrder\EmailSender;
use Magento\Sales\Model\Order;

class AddShippingDescription
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var MagentoVersion
     */
    private $magentoVersion;

    public function __construct(
        ConfigProvider $configProvider,
        MagentoVersion $magentoVersion
    ) {
        $this->configProvider = $configProvider;
        $this->magentoVersion = $magentoVersion;
    }

    /**
     * @param EmailSender $subject
     * @param Order $order
     * @return Order[]
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSend(EmailSender $subject, Order $order): array
    {
        $magentoVersion = $this->magentoVersion->get();
        if (!$this->configProvider->isStorePickupEnabled() || version_compare($magentoVersion, '2.4.4', '<')) {
            return [$order];
        }

        /* Call method to run our plugin Amasty\StorePickupWithLocator\Plugin\Sales\Model\AddShippingDescription
           to add Curbside Comment */
        $description = $order->getShippingDescription();

        $order->setShippingDescription($description);

        return [$order];
    }
}
