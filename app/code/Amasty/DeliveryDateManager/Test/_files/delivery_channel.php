<?php
declare(strict_types=1);
\Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize();

/** @var \Magento\TestFramework\ObjectManager $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$channelData = [
    [
        'channel_id' => 1,
        'limit_id' => 1,
        'config_id' => 1,
        'is_active' => true,
        'name' => 'test',
        'has_order_counter' => 0,
        'priority' => 1,
        'storeview_ids' => [1],
        'shipping_methods' => ['flatrate_flatrate'],
        'customer_groups' => []
    ]
];

foreach ($channelData as $data) {
    $moduleDataSetup = $objectManager->create(\Magento\Framework\Setup\ModuleDataSetupInterface::class);
    $moduleDataSetup->getConnection()->insertOnDuplicate(
        $moduleDataSetup->getTable(\Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel::MAIN_TABLE),
        ['channel_id' => 1]
    );

    /** @var \Amasty\DeliveryDateManager\Model\DeliveryChannel\DeliveryChannelData $deliveryChannel */
    $deliveryChannel = $objectManager->create(\Amasty\DeliveryDateManager\Model\DeliveryChannel\DeliveryChannelData::class);
    $deliveryChannel->setData($data);

    /** @var \Amasty\DeliveryDateManager\Model\DeliveryChannel\Save $deliveryChannelSaver */
    $deliveryChannelSaver = $objectManager->create(\Amasty\DeliveryDateManager\Model\DeliveryChannel\Save::class);
    $deliveryChannelSaver->execute($deliveryChannel);
}
