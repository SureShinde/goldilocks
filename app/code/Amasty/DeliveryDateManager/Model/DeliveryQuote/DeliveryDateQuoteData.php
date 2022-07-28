<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryQuote;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateQuoteInterface;
use Amasty\DeliveryDateManager\Model\AbstractTypifiedModel;

class DeliveryDateQuoteData extends AbstractTypifiedModel implements DeliveryDateQuoteInterface
{
    protected function _construct()
    {
        $this->_init(\Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryDateQuote::class);
    }

    /**
     * @return int
     */
    public function getDeliveryQuoteId(): int
    {
        return (int)$this->_getData(DeliveryDateQuoteInterface::DELIVERY_QUOTE_ID);
    }

    /**
     * @param int $deliveryQuoteId
     *
     * @return void
     */
    public function setDeliveryQuoteId(int $deliveryQuoteId): void
    {
        $this->setData(DeliveryDateQuoteInterface::DELIVERY_QUOTE_ID, $deliveryQuoteId);
    }

    /**
     * @return int
     */
    public function getQuoteId(): int
    {
        return (int)$this->_getData(DeliveryDateQuoteInterface::QUOTE_ID);
    }

    /**
     * @param int $quoteId
     *
     * @return void
     */
    public function setQuoteId(int $quoteId): void
    {
        $this->setData(DeliveryDateQuoteInterface::QUOTE_ID, $quoteId);
    }

    /**
     * @return int
     */
    public function getQuoteAddressId(): int
    {
        return (int)$this->_getData(DeliveryDateQuoteInterface::QUOTE_ADDRESS_ID);
    }

    /**
     * @param int $quoteAddressId
     *
     * @return void
     */
    public function setQuoteAddressId(int $quoteAddressId): void
    {
        $this->setData(DeliveryDateQuoteInterface::QUOTE_ADDRESS_ID, $quoteAddressId);
    }

    /**
     * @return string|null
     */
    public function getDate(): ?string
    {
        return $this->_getData(DeliveryDateQuoteInterface::DATE);
    }

    /**
     * @param string|null $date
     *
     * @return void
     */
    public function setDate(?string $date): void
    {
        $this->setData(DeliveryDateQuoteInterface::DATE, $date);
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->_getData(DeliveryDateQuoteInterface::COMMENT);
    }

    /**
     * @param string|null $comment
     *
     * @return void
     */
    public function setComment(?string $comment): void
    {
        $this->setData(DeliveryDateQuoteInterface::COMMENT, $comment);
    }

    /**
     * @return int|null
     */
    public function getTimeIntervalId(): ?int
    {
        $data = $this->_getData(DeliveryDateQuoteInterface::TIME_INTERVAL_ID);
        if ($data === null) {
            return null;
        }

        return (int)$data;
    }

    /**
     * @param int|null $timeIntervalId
     *
     * @return void
     */
    public function setTimeIntervalId(?int $timeIntervalId): void
    {
        $this->setData(DeliveryDateQuoteInterface::TIME_INTERVAL_ID, $timeIntervalId);
    }
}
