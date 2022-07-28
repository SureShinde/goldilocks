<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Plugin\Quote\Model\Quote;

use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\ToOrder;
use Magento\Sales\Api\Data\OrderInterface;

class AddressToOrderPlugin
{
    /**
     * @var \Magento\Sales\Api\Data\OrderExtensionFactory
     */
    private $orderExtensionFactory;

    /**
     * @var \Amasty\DeliveryDateManager\Model\DeliveryQuote\Validator
     */
    private $quoteValidator;

    /**
     * @var \Amasty\DeliveryDateManager\Model\DeliveryQuote\Get
     */
    private $getDeliveryQuote;

    /**
     * @var \Amasty\DeliveryDateManager\Model\ConfigProvider
     */
    private $configProvider;

    /**
     * @var \Amasty\DeliveryDateManager\Model\DeliveryOrder\CreateFromDeliveryQuote
     */
    private $converter;

    /**
     * @var \Amasty\DeliveryDateManager\Model\ChannelSetRepository
     */
    private $channelSetRepository;

    public function __construct(
        \Magento\Sales\Api\Data\OrderExtensionFactory $orderExtensionFactory,
        \Amasty\DeliveryDateManager\Model\DeliveryQuote\Validator $quoteValidator,
        \Amasty\DeliveryDateManager\Model\DeliveryQuote\Get $getDeliveryQuote,
        \Amasty\DeliveryDateManager\Model\ConfigProvider $configProvider,
        \Amasty\DeliveryDateManager\Model\DeliveryOrder\CreateFromDeliveryQuote $converter,
        \Amasty\DeliveryDateManager\Model\ChannelSetRepository $channelSetRepository
    ) {
        $this->orderExtensionFactory = $orderExtensionFactory;
        $this->quoteValidator = $quoteValidator;
        $this->getDeliveryQuote = $getDeliveryQuote;
        $this->configProvider = $configProvider;
        $this->converter = $converter;
        $this->channelSetRepository = $channelSetRepository;
    }

    /**
     * @param ToOrder $subject
     * @param OrderInterface $order
     * @param Address $quoteAddress
     *
     * @return OrderInterface
     */
    public function afterConvert(
        ToOrder $subject,
        OrderInterface $order,
        Address $quoteAddress
    ): OrderInterface {
        if (!$this->configProvider->isEnabled()) {
            return $order;
        }

        $deliveryQuote = $this->getDeliveryQuote->getByAddressId((int)$quoteAddress->getId());
        if (!$deliveryQuote->getQuoteAddressId()) {
            return $order;
        }

        $this->quoteValidator->validateDeliveryQuote($deliveryQuote);
        $deliveryOrder = $this->converter->deliveryQuoteToOrderQuote($deliveryQuote);
        $deliveryOrder->setIncrementId($order->getIncrementId());

        $orderAttributes = $order->getExtensionAttributes();
        if (empty($orderAttributes)) {
            $orderAttributes = $this->orderExtensionFactory->create();
        }
        $orderAttributes->setAmdeliverydate($deliveryOrder);
        $order->setExtensionAttributes($orderAttributes);

        return $order;
    }
}
