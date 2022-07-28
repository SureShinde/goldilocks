<?php

namespace Amasty\DeliveryDateManager\Setup;

use Amasty\DeliveryDateManager\Model\ResourceModel\ChannelConfig;
use Amasty\DeliveryDateManager\Model\ResourceModel\DateSchedule;
use Amasty\DeliveryDateManager\Model\ResourceModel\DateScheduleChannelRelation;
use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel;
use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryDateOrder;
use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryDateQuote;
use Amasty\DeliveryDateManager\Model\ResourceModel\OrderLimit;
use Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval;
use Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval\Set;
use Amasty\DeliveryDateManager\Model\ResourceModel\TimeIntervalChannelRelation;
use Amasty\DeliveryDateManager\Model\ResourceModel\TimeIntervalDateScheduleRelation;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    public function uninstall(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $setup->startSetup();
        $connection = $setup->getConnection();

        $connection->dropTable($installer->getTable(DateScheduleChannelRelation::MAIN_TABLE));
        $connection->dropTable($installer->getTable(DateSchedule::MAIN_TABLE));
        $connection->dropTable($installer->getTable(TimeInterval::LABEL_TABLE));
        $connection->dropTable($installer->getTable(TimeInterval::CHANNEL_RELATION_TABLE));
        $connection->dropTable($installer->getTable(TimeIntervalDateScheduleRelation::MAIN_TABLE));
        $connection->dropTable($installer->getTable(TimeIntervalChannelRelation::MAIN_TABLE));
        $connection->dropTable($installer->getTable(TimeInterval::MAIN_TABLE));
        $connection->dropTable($installer->getTable(Set::TIME_SET_RELATION_TABLE));
        $connection->dropTable($installer->getTable(Set::MAIN_TABLE));
        $connection->dropTable($installer->getTable(DeliveryChannel::SCOPE_STORE_TABLE));
        $connection->dropTable($installer->getTable(DeliveryChannel::SCOPE_SHIPPING_METHOD_TABLE));
        $connection->dropTable($installer->getTable(DeliveryChannel::SCOPE_CUSTOMER_GROUP_TABLE));
        $connection->dropTable($installer->getTable(DeliveryChannel::MAIN_TABLE));
        $connection->dropTable($installer->getTable(OrderLimit::MAIN_TABLE));
        $connection->dropTable($installer->getTable(ChannelConfig::MAIN_TABLE));
        $connection->dropTable($installer->getTable(DeliveryDateOrder::MAIN_TABLE));
        $connection->dropTable($installer->getTable(DeliveryDateQuote::MAIN_TABLE));

        $setup->endSetup();
    }
}
