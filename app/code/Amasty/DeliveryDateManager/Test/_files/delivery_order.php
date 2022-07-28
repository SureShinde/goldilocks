<?php
declare(strict_types=1);
\Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize();

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\Sales\Api\Data\OrderInterfaceFactory;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/order.php');


/** @var \Magento\TestFramework\ObjectManager $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Sales\Model\Order $order */
$order = $objectManager->get(OrderInterfaceFactory::class)->create()->loadByIncrementId('100000001');
$order->setCustomerId(1)->setCustomerIsGuest(false)->save();

/** @var \Magento\Sales\Model\Order $order */
$order = $objectManager->create(\Magento\Sales\Model\Order::class);
$order->load('100000001', 'increment_id');

$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime($today . "+1 days"));

$deliveryOrderData = [
    [
        'deliverydate_id' => 1,
        'counter_id' => 0,
        'order_id' => $order->getId(),
        'increment_id' => $order->getIncrementId(),
        'date' => $tomorrow,
        'time_from' => 30,
        'time_to' => 120,
        'comment' => '123',
        'reminder' => '',
        'time_interval_id' => 1,
        'active' => 1,
    ]
];

foreach ($deliveryOrderData as $data) {
    $moduleDataSetup = $objectManager->create(\Magento\Framework\Setup\ModuleDataSetupInterface::class);
    $moduleDataSetup->getConnection()->insertOnDuplicate(
        $moduleDataSetup->getTable(\Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryDateOrder::MAIN_TABLE),
        $data
    );
}
