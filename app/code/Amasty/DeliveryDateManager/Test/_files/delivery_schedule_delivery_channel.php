<?php
declare(strict_types=1);
\Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize();

/** @var \Magento\TestFramework\ObjectManager $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$dateScheduleDeliveryChannelData = [
    [
        'relation_id' => 1,
        'delivery_channel_id' => 1,
        'date_schedule_id' => 1,
    ]
];

foreach ($dateScheduleDeliveryChannelData as $data) {
    $moduleDataSetup = $objectManager->create(\Magento\Framework\Setup\ModuleDataSetupInterface::class);
    $moduleDataSetup->getConnection()->insertOnDuplicate(
        $moduleDataSetup->getTable(\Amasty\DeliveryDateManager\Model\ResourceModel\DateScheduleChannelRelation::MAIN_TABLE),
        $data
    );
}
