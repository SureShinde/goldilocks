<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryOrder;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateOrderInterface;
use Amasty\DeliveryDateManager\Api\Data\DeliveryDateQuoteInterface;
use Amasty\DeliveryDateManager\Model\ChannelSetProcessor;
use Amasty\DeliveryDateManager\Model\ChannelSetRepository;
use Amasty\DeliveryDateManager\Model\DeliveryChannelScope\ScopeRegistry;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\DeliveryOrderDataFactory;
use Amasty\DeliveryDateManager\Model\TimeInterval\Get as GetTimeInterval;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Convert DeliveryDateOrder to DeliveryDateQuote
 */
class CreateFromDeliveryQuote
{
    /**
     * @var DeliveryOrderDataFactory
     */
    private $deliveryOrderFactory;

    /**
     * @var GetTimeInterval
     */
    private $getTimeInterval;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var ScopeRegistry
     */
    private $scopeRegistry;

    /**
     * @var ChannelSetRepository
     */
    private $channelSetRepository;

    /**
     * @var ChannelSetProcessor
     */
    private $channelSetProcessor;

    public function __construct(
        DeliveryOrderDataFactory $deliveryOrderFactory,
        GetTimeInterval $getTimeInterval,
        CartRepositoryInterface $quoteRepository,
        ScopeRegistry $scopeRegistry,
        ChannelSetRepository $channelSetRepository,
        ChannelSetProcessor $channelSetProcessor
    ) {
        $this->deliveryOrderFactory = $deliveryOrderFactory;
        $this->getTimeInterval = $getTimeInterval;
        $this->quoteRepository = $quoteRepository;
        $this->scopeRegistry = $scopeRegistry;
        $this->channelSetRepository = $channelSetRepository;
        $this->channelSetProcessor = $channelSetProcessor;
    }

    /**
     * @param DeliveryDateQuoteInterface $deliveryQuote
     *
     * @return DeliveryDateOrderInterface
     */
    public function deliveryQuoteToOrderQuote(DeliveryDateQuoteInterface $deliveryQuote): DeliveryDateOrderInterface
    {
        $quote = $this->quoteRepository->get($deliveryQuote->getQuoteId());
        $address = $quote->getAddressById($deliveryQuote->getQuoteAddressId());

        $this->scopeRegistry->reset();
        $this->scopeRegistry->collectScopesFromQuoteAddress($address);

        $channelSet = $this->channelSetRepository->getByScope();
        $this->channelSetProcessor->setChannelSetResult($channelSet);

        /** @var DeliveryDateOrderInterface $deliveryOrder */
        $deliveryOrder = $this->deliveryOrderFactory->create();
        $deliveryOrder->setComment($deliveryQuote->getComment());
        $deliveryOrder->setDate($deliveryQuote->getDate());

        if ($timeId = $deliveryQuote->getTimeIntervalId()) {
            $interval = $this->getTimeInterval->execute($timeId);
            $deliveryOrder->setTimeIntervalId($deliveryQuote->getTimeIntervalId());
            $deliveryOrder->setTimeFrom($interval->getFrom());
            $deliveryOrder->setTimeTo($interval->getTo());
        }

        $deliveryOrder->setCounterId($this->channelSetProcessor->getCounterId());

        return $deliveryOrder;
    }
}
