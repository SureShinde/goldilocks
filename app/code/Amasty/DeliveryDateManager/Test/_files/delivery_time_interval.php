<?php
declare(strict_types=1);
\Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize();

/** @var \Magento\TestFramework\ObjectManager $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$timeIntervalData = [
    [
        'interval_id' => 1,
        'limit_id' => 1,
        'from' => 30,
        'to' => 120,
        'position' => 1
    ],
];

foreach ($timeIntervalData as $data) {
    /** @var \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup */
    $moduleDataSetup = $objectManager->create(\Magento\Framework\Setup\ModuleDataSetupInterface::class);
    $moduleDataSetup->getConnection()->insertOnDuplicate(
        $moduleDataSetup->getTable(\Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval::MAIN_TABLE),
        $data
    );
}
