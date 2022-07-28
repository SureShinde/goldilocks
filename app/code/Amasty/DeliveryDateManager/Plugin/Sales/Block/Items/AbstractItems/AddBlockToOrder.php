<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Plugin\Sales\Block\Items\AbstractItems;

use Amasty\DeliveryDateManager\Model\ConfigProvider;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\Get;
use Magento\Framework\Exception\NoSuchEntityException;

class AddBlockToOrder
{
    /**
     * @var \Psr\Log\LoggerInterface
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

    /**
     * @var bool
     */
    private $isApplied = false;

    public function __construct(
        ConfigProvider $configProvider,
        Get $getDeliveryOrder,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->configProvider = $configProvider;
        $this->getDeliveryOrder = $getDeliveryOrder;
        $this->logger = $logger;
    }

    /**
     * Plugin to abstract class \Magento\Sales\Block\Items\AbstractItems only for front scope
     *
     * @param \Magento\Sales\Block\Items\AbstractItems $subject
     * @param string $result
     *
     * @return string
     */
    public function afterToHtml(\Magento\Sales\Block\Items\AbstractItems $subject, string $result): string
    {
        // on order print page there are lots of AbstractItems blocks, but we need to apply DD Info only once
        if ($this->isApplied) {
            return $result;
        }

        if (!$subject->getOrder()
            || !$subject->getOrder()->getId()
            || ($subject instanceof \Magento\Sales\Block\Order\Email\Creditmemo\Items)
        ) {
            return $result;
        }

        if (!$this->configProvider->isEnabled($subject->getOrder()->getStoreId())) {
            return $result;
        }

        try {
            $deliveryDate = $this->getDeliveryOrder->getByOrderId((int)$subject->getOrder()->getId());
        } catch (NoSuchEntityException $e) {
            return $result;
        }

        try {
            $addToResult = $subject->getLayout()
                ->createBlock(
                    \Amasty\DeliveryDateManager\Block\Sales\Order\Info\Deliverydate::class,
                    'deliverydate_info',
                    [
                        'data' => [
                            'order' => $subject->getOrder(),
                            'delivery_date' => $deliveryDate,
                        ]
                    ]
                )
                ->toHtml();
            $this->isApplied = true;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->error($e->getLogMessage());
            $addToResult = '';
        }

        return $addToResult . $result;
    }
}
