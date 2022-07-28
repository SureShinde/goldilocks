<?php

declare(strict_types=1);

namespace Amasty\PreOrderRelease\Model\Notification;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

class ProcessCollection
{
    /**
     * @var GetCustomersToSend
     */
    private $getCustomersToSend;

    /**
     * @var SendEmail
     */
    private $sendEmail;

    public function __construct(GetCustomersToSend $getCustomersToSend, SendEmail $sendEmail)
    {
        $this->getCustomersToSend = $getCustomersToSend;
        $this->sendEmail = $sendEmail;
    }

    /**
     * @param ProductCollection $productCollection
     * @return ProductInterface[]
     */
    public function execute(ProductCollection $productCollection): array
    {
        $productsSend = [];
        $productIds = array_map(function ($product) {
            return $product->getId();
        }, $productCollection->getItems());
        $customers = $this->getCustomersToSend->execute($productIds);
        foreach ($customers as $data) {
            $product = $productCollection->getItemById($data[GetCustomersToSend::PRODUCT_ID_KEY]);
            $this->sendEmail->execute(
                $data[GetCustomersToSend::CUSTOMER_EMAIL_KEY],
                (int) $data[GetCustomersToSend::STORE_ID_KEY],
                $data[GetCustomersToSend::PRODUCT_NAME_KEY]
            );
            $productsSend[$product->getId()] = $product;
        }

        return $productsSend;
    }
}
