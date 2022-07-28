<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryQuote;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateQuoteInterface;
use Amasty\DeliveryDateManager\Model\ChannelSetRepository;
use Amasty\DeliveryDateManager\Model\ConfigProvider;
use Amasty\DeliveryDateManager\Model\DeliveryChannelScope\ScopeRegistry;
use Amasty\DeliveryDateManager\Model\DeliveryDate\Validator as DeliveryValidator;
use Amasty\DeliveryDateManager\Model\TimeInterval\Get as GetTimeInterval;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Validation\ValidationException;
use Magento\Quote\Api\CartRepositoryInterface;

class Validator
{
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
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var DeliveryValidator
     */
    private $validator;

    /**
     * @var GetTimeInterval
     */
    private $getTimeInterval;

    public function __construct(
        CartRepositoryInterface $quoteRepository,
        ScopeRegistry $scopeRegistry,
        ChannelSetRepository $channelSetRepository,
        ConfigProvider $configProvider,
        DeliveryValidator $dateValidator,
        GetTimeInterval $getTimeInterval
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->scopeRegistry = $scopeRegistry;
        $this->channelSetRepository = $channelSetRepository;
        $this->configProvider = $configProvider;
        $this->validator = $dateValidator;
        $this->getTimeInterval = $getTimeInterval;
    }

    /**
     * @param DeliveryDateQuoteInterface $deliveryQuote
     *
     * @return bool
     * @throws InputException
     * @throws ValidationException
     */
    public function validateDeliveryQuote(DeliveryDateQuoteInterface $deliveryQuote): bool
    {
        $quote = $this->quoteRepository->get($deliveryQuote->getQuoteId());
        $address = $quote->getAddressById($deliveryQuote->getQuoteAddressId());
        $storeId = (int)$quote->getStoreId();

        if (!$address || $address->getId() != $deliveryQuote->getQuoteAddressId()) {
            throw new ValidationException(
                __('Shipping Address is not in quote')
            );
        }

        $this->scopeRegistry->reset();
        $this->scopeRegistry->collectScopesFromQuoteAddress($address);
        $channelSet = $this->channelSetRepository->getByScope();
        $hasChannels = (bool)$channelSet->getDeliveryChannel()->getTotalCount();

        if ($hasChannels) {
            $this->validator->validateForRequired($deliveryQuote, $storeId);
            $this->deliveryDateValidate($deliveryQuote, $storeId);
            $this->deliveryTimeValidate($deliveryQuote, $storeId);
            $this->deliveryCommentValidate($deliveryQuote, $storeId);
        } else {
            $deliveryQuote->setDate(null);
            $deliveryQuote->setTimeIntervalId(null);
            $deliveryQuote->setComment(null);
        }

        return true;
    }

    /**
     * @param DeliveryDateQuoteInterface $deliveryQuote
     * @param int $storeId
     *
     * @throws ValidationException
     */
    private function deliveryDateValidate(DeliveryDateQuoteInterface $deliveryQuote, int $storeId): void
    {
        if (!$deliveryQuote->getDate() && !$this->configProvider->isDateRequired($storeId)) {
            return;
        }

        $channelSet = $this->channelSetRepository->getByScope();
        $date = date('Y-m-d', strtotime($deliveryQuote->getDate()));
        $validationResult = $this->validator->validate($channelSet, $date);

        if (!$validationResult->isValid()) {
            throw new ValidationException(
                __('Delivery Date Validation Failed'),
                null,
                0,
                $validationResult
            );
        }

        $deliveryQuote->setDate($date);
    }

    /**
     * @param DeliveryDateQuoteInterface $deliveryQuote
     * @param int $storeId
     *
     * @throws ValidationException
     */
    private function deliveryTimeValidate(DeliveryDateQuoteInterface $deliveryQuote, int $storeId): void
    {
        if (!$this->configProvider->isTimeEnabled($storeId)) {
            $deliveryQuote->setTimeIntervalId(null);

            return;
        }

        if (!$deliveryQuote->getTimeIntervalId() && !$this->configProvider->isTimeRequired($storeId)) {
            return;
        }

        $channelSet = $this->channelSetRepository->getByScope();
        $timeInterval = $this->getTimeInterval->execute($deliveryQuote->getTimeIntervalId());
        $validationResult = $this->validator->validateTimeInterval(
            $channelSet,
            $deliveryQuote->getDate(),
            $timeInterval->getFrom(),
            $timeInterval->getTo()
        );

        if (!$validationResult->isValid()) {
            throw new ValidationException(
                __('Delivery Time Validation Failed'),
                null,
                0,
                $validationResult
            );
        }
    }

    /**
     * @param DeliveryDateQuoteInterface $deliveryQuote
     * @param int $storeId
     *
     * @throws InputException
     */
    private function deliveryCommentValidate(DeliveryDateQuoteInterface $deliveryQuote, int $storeId): void
    {
        if ($this->configProvider->isCommentEnabled($storeId)) {
            $max = $this->configProvider->getCommentMaxLength($storeId);

            if ($max && strlen((string)$deliveryQuote->getComment()) > $max) {
                throw new InputException(__('Delivery Comment field is too long.'));
            }
        } else {
            $deliveryQuote->setComment(null);
        }
    }
}
