<?php
declare(strict_types=1);
\Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize();

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/quote_with_customer.php');

/** @var \Magento\TestFramework\ObjectManager $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$quote = $objectManager->create(\Magento\Quote\Model\Quote::class);
$quote->load('test01', 'reserved_order_id');

$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime($today . "+1 days"));

$deliveryQuoteData = [
    [
        'delivery_quote_id' => 1,
        'quote_id' => $quote->getId(),
        'quote_address_id' => $quote->getShippingAddress()->getId(),
        'date' => $tomorrow,
        'comment' => 'test',
        'time_interval_id' => 1
    ]
];

foreach ($deliveryQuoteData as $data) {
    $moduleDataSetup = $objectManager->create(\Magento\Framework\Setup\ModuleDataSetupInterface::class);
    $moduleDataSetup->getConnection()->insertOnDuplicate(
        $moduleDataSetup->getTable(\Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryDateQuote::MAIN_TABLE),
        $data
    );
}
