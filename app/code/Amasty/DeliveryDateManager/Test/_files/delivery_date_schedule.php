<?php
declare(strict_types=1);
\Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize();
use Magento\Ui\Component\Form\Element\DataType\Date;

/** @var \Magento\TestFramework\ObjectManager $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime($today . "+1 days"));
$dateSchedule = [
    [
        'schedule_id' => 1,
        'name' => 'test1',
        'limit_id' => 1,
        'type' => 0,
        'from' => $today,
        'to' => $tomorrow,
        'is_available' => 1
    ]
];

foreach ($dateSchedule as $data) {
    /** @var \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup */
    $moduleDataSetup = $objectManager->create(\Magento\Framework\Setup\ModuleDataSetupInterface::class);
    $moduleDataSetup->getConnection()->insertOnDuplicate(
        $moduleDataSetup->getTable(\Amasty\DeliveryDateManager\Model\ResourceModel\DateSchedule::MAIN_TABLE),
        $data
    );
}
