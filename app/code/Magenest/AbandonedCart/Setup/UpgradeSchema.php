<?php

namespace Magenest\AbandonedCart\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        // Check the versions
        if (version_compare($context->getVersion(), '1.1.0') < 0) {
            $this->createABTestCampaign($setup);
            $this->addColumnCountDuplicate($setup);
            $this->addColumnRuleId($setup);
        }
    }

    /**
     * @param $setup
     */
    private function createABTestCampaign($setup)
    {
        $installer = $setup;
        $installer->startSetup();
        $table = $installer->getConnection()->newTable(
            $installer->getTable("magenest_abacar_abtest_campaign")
        )
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'A/B Test Campaign ID'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                [
                'nullable' => false
                ],
                'A/B Test Campaign Status'
            )->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'A/B Test Campaign Name'
            )->addColumn(
                'description',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'A/B Test Campaign Description'
            )->addColumn(
                'from_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                [
                'nullable' => true,
                'default' => null,
                ],
                'From Date'
            )->addColumn(
                'to_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                [
                    'nullable' => true,
                    'default' => null,
                ],
                'To Date'
            );
        $installer->getConnection()->createTable($table);
    }

    /**
     * @param $setup
     */
    function addColumnCountDuplicate($setup)
    {
        $installer = $setup;
        $installer->startSetup();
        $installer->getConnection()->addColumn(
            $installer->getTable('magenest_abacar_rule'),
            'duplicate',
            [
                'type' => Table::TYPE_INTEGER,
                'nullable' => true,
                'default' => 0,
                'comment' => 'Count number for duplicate rule',
            ]
        );
    }

    public function addColumnRuleId($setup)
    {
        $installer = $setup;
        $installer->startSetup();
        $installer->getConnection()->addColumn(
            $installer->getTable('magenest_abacar_list'),
            'rule_id',
            [
                'type' => Table::TYPE_INTEGER,
                'nullable' => true,
                'default' => null,
                'comment' => 'Rule ID send mail for customer recovered cart',
            ]
        );
    }
}
