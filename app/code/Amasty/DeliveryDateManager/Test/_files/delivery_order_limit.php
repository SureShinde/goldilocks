<?php
declare(strict_types=1);

use Amasty\DeliveryDateManager\Model\ResourceModel\OrderLimit;

\Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize();

/** @var \Magento\TestFramework\ObjectManager $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$orderLimitData = [
    [
        'limit_id' => 1,
        'name' => 'test1',
        'day_limit' => 1,
        'interval_limit' => 1,
    ]
];

foreach ($orderLimitData as $data) {

    /** @var \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup */
    $moduleDataSetup = $objectManager->create(\Magento\Framework\Setup\ModuleDataSetupInterface::class);
    $moduleDataSetup->getConnection()->insertOnDuplicate(
        $moduleDataSetup->getTable(OrderLimit::MAIN_TABLE),
        $data
    );
}
