<?php
declare(strict_types=1);
\Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize();

/** @var \Magento\TestFramework\ObjectManager $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$timeIntervalScheduleData = [
    [
        'relation_id' => 1,
        'date_schedule_id' => 1,
        'time_interval_id' => 1
    ],

];

foreach ($timeIntervalScheduleData as $data) {
    /** @var \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup */
    $moduleDataSetup = $objectManager->create(\Magento\Framework\Setup\ModuleDataSetupInterface::class);
    $moduleDataSetup->getConnection()->insertOnDuplicate(
        $moduleDataSetup->getTable(
            \Amasty\DeliveryDateManager\Model\ResourceModel\TimeIntervalDateScheduleRelation::MAIN_TABLE
        ),
        $data
    );
}
