<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryQuote;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateQuoteInterface;
use Amasty\DeliveryDateManager\Api\DeliveryQuoteServiceInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;

class Manager implements DeliveryQuoteServiceInterface
{
    /**
     * @var Get
     */
    private $getDeliveryQuote;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var Save
     */
    private $save;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var Escaper
     */
    private $escaper;

    public function __construct(
        Get $getDeliveryQuote,
        CartRepositoryInterface $addressManagement,
        Save $save,
        Validator $validator,
        Escaper $escaper
    ) {
        $this->getDeliveryQuote = $getDeliveryQuote;
        $this->quoteRepository = $addressManagement;
        $this->save = $save;
        $this->validator = $validator;
        $this->escaper = $escaper;
    }

    /**
     * @param int $cartId
     * @param int $quoteAddressId
     *
     * @return DeliveryDateQuoteInterface
     * @throws NoSuchEntityException
     */
    public function getFromQuoteAddressId(
        int $cartId,
        int $quoteAddressId
    ): DeliveryDateQuoteInterface {
        $quote = $this->quoteRepository->get($cartId);
        $address = $quote->getAddressById($quoteAddressId);
        if (!$address) {
            throw NoSuchEntityException::doubleField('quoteAddressId', $quoteAddressId, 'cartId', $cartId);
        }

        $deliveryQuote = $this->getDeliveryQuote->getByAddressId((int)$address->getId());
        if ($deliveryQuote->getDeliveryQuoteId() === 0) {
            throw NoSuchEntityException::singleField('quoteAddressId', $quoteAddressId);
        }

        return $deliveryQuote;
    }

    /**
     * @param int $cartId
     * @param int $quoteAddressId
     * @param string|null $date
     * @param int|null $timeIntervalId
     * @param string|null $comment
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function validate(
        int $cartId,
        int $quoteAddressId,
        ?string $date,
        ?int $timeIntervalId = null,
        ?string $comment = null
    ): bool {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->get($cartId);
        $address = $quote->getAddressById($quoteAddressId);
        if (!$address) {
            throw NoSuchEntityException::doubleField('quoteAddressId', $quoteAddressId, 'cartId', $cartId);
        }

        $deliveryQuote = $this->getDeliveryQuote->getByAddressId((int)$address->getId());

        $deliveryQuote->setQuoteId($cartId);
        $deliveryQuote->setQuoteAddressId($quoteAddressId);
        $deliveryQuote->setDate($date);
        $deliveryQuote->setTimeIntervalId($timeIntervalId);
        $deliveryQuote->setComment($comment);

        return $this->validator->validateDeliveryQuote($deliveryQuote);
    }

    /**
     * @param int $cartId
     * @param int $quoteAddressId
     * @param string|null $date - in ISO format 'yyyy-mm-dd'
     * @param int|null $timeIntervalId
     * @param string|null $comment
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function saveForQuoteAddress(
        int $cartId,
        int $quoteAddressId,
        ?string $date,
        ?int $timeIntervalId = null,
        ?string $comment = null
    ): bool {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->get($cartId);
        $address = $quote->getAddressById($quoteAddressId);
        if (!$address) {
            throw NoSuchEntityException::doubleField('quoteAddressId', $quoteAddressId, 'cartId', $cartId);
        }

        $deliveryQuote = $this->getDeliveryQuote->getByAddressId((int)$address->getId());

        $deliveryQuote->setQuoteId($cartId);
        $deliveryQuote->setQuoteAddressId($quoteAddressId);
        $deliveryQuote->setDate($date);
        $deliveryQuote->setTimeIntervalId($timeIntervalId);
        $deliveryQuote->setComment($this->escaper->escapeHtml((string)$comment));

        $this->validator->validateDeliveryQuote($deliveryQuote);

        $this->save->execute($deliveryQuote);

        return true;
    }
}
