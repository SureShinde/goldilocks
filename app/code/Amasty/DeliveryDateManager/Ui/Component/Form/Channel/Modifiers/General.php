<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Ui\Component\Form\Channel\Modifiers;

use Amasty\DeliveryDateManager\Api\Data\DeliveryChannelInterface;
use Amasty\DeliveryDateManager\Model\DeliveryChannel\Get;
use Amasty\DeliveryDateManager\Ui\Component\AbstractModifier;
use Magento\Framework\App\RequestInterface;

class General extends AbstractModifier
{
    /**
     * @var Get
     */
    private $getDeliveryChannel;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        Get $getDeliveryChannel,
        RequestInterface $request
    ) {
        $this->getDeliveryChannel = $getDeliveryChannel;
        $this->request = $request;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function modifyData(array $data): array
    {
        $channelId = $this->request->getParam(self::CHANNEL_REQUEST_ID);

        if ($channelId) {
            $deliveryChannel = $this->getDeliveryChannel->execute((int)$channelId);
            $data[$channelId] = $deliveryChannel->getData();
            $data[$channelId][DeliveryChannelInterface::IS_ACTIVE] = (string)(int)$deliveryChannel->getIsActive();
            $data[$channelId][DeliveryChannelInterface::CONFIG_ID] = (string)$deliveryChannel->getConfigId();
        }

        return $data;
    }

    /**
     * @param array $meta
     *
     * @return array
     */
    public function modifyMeta(array $meta): array
    {
        return $meta;
    }
}
