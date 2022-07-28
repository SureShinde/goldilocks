<?php
declare(strict_types=1);
\Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize();

/** @var \Magento\TestFramework\ObjectManager $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$channelConfigArray = [
    [
        'id' => 1,
        'name' => 'test1',
        'min' => 1,
        'max' => 1,
        'is_same_day_available' => true,
        'same_day_cutoff' => 0,
        'order_time' => 0,
        'backorder_time' => 0
    ]
];

foreach ($channelConfigArray as $data) {
    $moduleDataSetup = $objectManager->create(\Magento\Framework\Setup\ModuleDataSetupInterface::class);
    $moduleDataSetup->getConnection()->insertOnDuplicate(
        $moduleDataSetup->getTable(\Amasty\DeliveryDateManager\Model\ResourceModel\ChannelConfig::MAIN_TABLE),
        $data
    );
}
