<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryQuote;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateQuoteInterface;
use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryDateQuote as DeliveryDateQuoteResource;
use Magento\Framework\Exception\CouldNotSaveException;

class Save
{
    /**
     * @var DeliveryDateQuoteResource
     */
    private $deliveryDateQuoteResource;

    /**
     * @var Get
     */
    private $get;

    public function __construct(
        DeliveryDateQuoteResource $deliveryDateQuoteResource,
        Get $get
    ) {
        $this->deliveryDateQuoteResource = $deliveryDateQuoteResource;
        $this->get = $get;
    }

    /**
     * @param DeliveryDateQuoteInterface $deliveryQuoteModel
     *
     * @return DeliveryDateQuoteInterface
     * @throws CouldNotSaveException
     */
    public function execute(DeliveryDateQuoteInterface $deliveryQuoteModel) :DeliveryDateQuoteInterface
    {
        try {
            if (!$deliveryQuoteModel->getDeliveryQuoteId()) {
                $this->get->clearStorage($deliveryQuoteModel->getQuoteAddressId());
                $deliveryQuoteModel = $this->get->getByAddressId($deliveryQuoteModel->getQuoteAddressId())
                    ->addData($deliveryQuoteModel->getData());
            }

            $this->deliveryDateQuoteResource->save($deliveryQuoteModel);
        } catch (\Exception $e) {
            if ($deliveryQuoteModel->getDeliveryQuoteId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save delivery quote with ID %1. Error: %2',
                        [$deliveryQuoteModel->getDeliveryQuoteId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new delivery quote. Error: %1', $e->getMessage()));
        }

        return $deliveryQuoteModel;
    }
}
