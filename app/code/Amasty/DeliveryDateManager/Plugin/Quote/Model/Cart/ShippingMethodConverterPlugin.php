<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Plugin\Quote\Model\Cart;

use Amasty\DeliveryDateManager\Model\ChannelSetCollector;
use Amasty\DeliveryDateManager\Model\DeliveryChannelScope\ScopeRegistry;
use Amasty\DeliveryDateManager\Model\DeliveryChannelScope\ShippingMethodScopeData;
use Amasty\DeliveryDateManager\Model\OrderLimit\Restricted\RestrictedDateProvider;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Model\Cart\ShippingMethodConverter;

class ShippingMethodConverterPlugin
{
    /**
     * @var ExtensionAttributesFactory
     */
    private $attributesFactory;

    /**
     * @var ScopeRegistry
     */
    private $scopeRegistry;

    /**
     * @var RestrictedDateProvider
     */
    private $restrictedDateProvider;

    /**
     * @var \Amasty\DeliveryDateManager\Model\ChannelSetRepository
     */
    private $channelSetRepository;

    public function __construct(
        ExtensionAttributesFactory $attributesFactory,
        ScopeRegistry $scopeRegistry,
        RestrictedDateProvider $restrictedDateProvider,
        \Amasty\DeliveryDateManager\Model\ChannelSetRepository $channelSetRepository
    ) {
        $this->attributesFactory = $attributesFactory;
        $this->scopeRegistry = $scopeRegistry;
        $this->restrictedDateProvider = $restrictedDateProvider;
        $this->channelSetRepository = $channelSetRepository;
    }

    /**
     * @param ShippingMethodConverter $subject
     * @param ShippingMethodInterface $shippingMethod
     *
     * @return ShippingMethodInterface
     */
    public function afterModelToDataObject(ShippingMethodConverter $subject, ShippingMethodInterface $shippingMethod)
    {
        $carrierMethodCode = $shippingMethod->getCarrierCode() . '_' . $shippingMethod->getMethodCode();
        $this->scopeRegistry->setScope(ShippingMethodScopeData::SCOPE_CODE, $carrierMethodCode);

        $channelSet = $this->channelSetRepository->getByScope();
        $restrictedDays = $this->restrictedDateProvider->getRestrictedByChannelSet($channelSet);

        $extAttributes = $shippingMethod->getExtensionAttributes();
        if ($extAttributes === null) {
            $extAttributes = $this->attributesFactory
                ->create(\Magento\Quote\Api\Data\ShippingMethodInterface::class);
        }

        $extAttributes->setAmdeliverydateChannels($channelSet->getDeliveryChannel()->getItems());
        $extAttributes->setAmdeliverydateChannelConfig($channelSet->getChannelConfig());
        $extAttributes->setAmdeliverydateDateChannelLinks($channelSet->getDateChannelLinks()->getItems());
        $extAttributes->setAmdeliverydateDateScheduleItems($channelSet->getDateScheduleItems()->getItems());
        $extAttributes->setAmdeliverydateTimeChannelLinks($channelSet->getTimeChannelLinks()->getItems());
        $extAttributes->setAmdeliverydateTimeScheduleLinks($channelSet->getTimeDateLinks()->getItems());
        $extAttributes->setAmdeliverydateTimeIntervalItems($channelSet->getTimeIntervalItems()->getItems());
        $extAttributes->setAmdeliverydateDisabledDaysByLimit($restrictedDays);

        $shippingMethod->setExtensionAttributes($extAttributes);

        return $shippingMethod;
    }
}
