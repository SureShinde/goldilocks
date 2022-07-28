<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryOrder;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateOrderInterface;
use Amasty\DeliveryDateManager\Model\ChannelSetRepository;
use Amasty\DeliveryDateManager\Model\ConfigProvider;
use Amasty\DeliveryDateManager\Model\DeliveryChannelScope\ScopeRegistry;
use Amasty\DeliveryDateManager\Model\DeliveryDate\Validator as DeliveryValidator;
use Amasty\DeliveryDateManager\Model\TimeInterval\Get as GetTimeInterval;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Validation\ValidationException;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * @TODO: Need to refactor validators structure to prevent code duplication for order and quote objects
 */
class Validator
{
    /**
     * @var ScopeRegistry
     */
    private $scopeRegistry;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var ChannelSetRepository
     */
    private $channelSetRepository;

    /**
     * @var DeliveryValidator
     */
    private $validator;

    /**
     * @var GetTimeInterval
     */
    private $getTimeInterval;

    /**
     * @var StringUtils
     */
    private $stringUtils;

    public function __construct(
        ScopeRegistry $scopeRegistry,
        OrderRepositoryInterface $orderRepository,
        ConfigProvider $configProvider,
        ChannelSetRepository $channelSetRepository,
        DeliveryValidator $validator,
        GetTimeInterval $getTimeInterval,
        StringUtils $stringUtils
    ) {
        $this->scopeRegistry = $scopeRegistry;
        $this->orderRepository = $orderRepository;
        $this->configProvider = $configProvider;
        $this->channelSetRepository = $channelSetRepository;
        $this->validator = $validator;
        $this->getTimeInterval = $getTimeInterval;
        $this->stringUtils = $stringUtils;
    }

    /**
     * @param DeliveryDateOrderInterface $deliveryDateOrder
     * @return void
     * @throws InputException
     * @throws ValidationException
     */
    public function validateDeliveryOrder(DeliveryDateOrderInterface $deliveryDateOrder): void
    {
        $order = $this->orderRepository->get($deliveryDateOrder->getOrderId());
        $storeId = (int)$order->getStoreId();
        $this->scopeRegistry->reset();
        $this->scopeRegistry->collectScopesFromOrder($order);
        $channelSet = $this->channelSetRepository->getByScope();
        $hasChannels = (bool)$channelSet->getDeliveryChannel()->getTotalCount();

        if ($hasChannels) {
            $this->validator->validateForRequired($deliveryDateOrder, $storeId);
            $this->deliveryDateValidate($deliveryDateOrder, $storeId);
            $this->deliveryTimeValidate($deliveryDateOrder, $storeId);
            $this->deliveryCommentValidate($deliveryDateOrder, $storeId);
        } else {
            $deliveryDateOrder->setDate(null);
            $deliveryDateOrder->setTimeFrom(null);
            $deliveryDateOrder->setTimeTo(null);
            $deliveryDateOrder->setTimeIntervalId(null);
            $deliveryDateOrder->setComment(null);
        }
    }

    /**
     * @param DeliveryDateOrderInterface $deliveryDateOrder
     * @param int $storeId
     * @return void
     *
     * @throws InputException
     * @throws ValidationException
     */
    private function deliveryDateValidate(DeliveryDateOrderInterface $deliveryDateOrder, int $storeId): void
    {
        if (!$deliveryDateOrder->getDate() && !$this->configProvider->isDateRequired($storeId)) {
            return;
        }
        $channelSet = $this->channelSetRepository->getByScope();
        $date = date('Y-m-d', strtotime($deliveryDateOrder->getDate()));
        $validationResult = $this->validator->validate($channelSet, $date);
        if (!$validationResult->isValid()) {
            throw new ValidationException(
                __('Delivery Date Validation Failed'),
                null,
                0,
                $validationResult
            );
        }
    }

    /**
     * @param DeliveryDateOrderInterface $deliveryDateOrder
     * @param int $storeId
     * @return void
     *
     * @throws InputException
     * @throws ValidationException
     */
    private function deliveryTimeValidate(DeliveryDateOrderInterface $deliveryDateOrder, int $storeId): void
    {
        if (!$this->configProvider->isTimeEnabled($storeId)) {
            $deliveryDateOrder->setTimeIntervalId(null);
            $deliveryDateOrder->setTimeFrom(null);
            $deliveryDateOrder->setTimeTo(null);

            return;
        }
        if (!$deliveryDateOrder->getTimeIntervalId() && !$this->configProvider->isTimeRequired($storeId)) {
            return;
        }

        $channelSet = $this->channelSetRepository->getByScope();
        $timeInterval = $this->getTimeInterval->execute($deliveryDateOrder->getTimeIntervalId());
        $validationResult = $this->validator->validateTimeInterval(
            $channelSet,
            $deliveryDateOrder->getDate(),
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
     * @param DeliveryDateOrderInterface $deliveryDateOrder
     * @param int $storeId
     * @return void
     *
     * @throws InputException
     */
    private function deliveryCommentValidate(DeliveryDateOrderInterface $deliveryDateOrder, int $storeId): void
    {
        if (!$this->configProvider->isCommentEnabled($storeId)) {
            $deliveryDateOrder->setComment("");

            return;
        }

        $max = $this->configProvider->getCommentMaxLength($storeId);
        $commentLength = $this->stringUtils->strlen($deliveryDateOrder->getComment());
        if ($max > 0 && $commentLength > $max) {
            throw new InputException(__('Delivery Comment field is too long.'));
        }
    }
}
