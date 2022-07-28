<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_ShippingRestriction
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ShippingRestriction\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            if ($installer->tableExists('mageplaza_shippingrestriction_rule')) {
                $columns = [
                    'discard_sub_rule' => [
                        'type'     => Table::TYPE_BOOLEAN,
                        'comment'  => 'Discard Subsequent Rules',
                        'unsigned' => true
                    ],
                ];
                $table = $installer->getTable('mageplaza_shippingrestriction_rule');
                foreach ($columns as $name => $definition) {
                    $connection->addColumn($table, $name, $definition);
                }
            }
        }

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            if ($installer->tableExists('mageplaza_shippingrestriction_rule')) {
                $connection->modifyColumn(
                    $installer->getTable('mageplaza_shippingrestriction_rule'),
                    'started_at',
                    [
                        'type' => Table::TYPE_DATE
                    ]
                );
                $connection->modifyColumn(
                    $installer->getTable('mageplaza_shippingrestriction_rule'),
                    'finished_at',
                    [
                        'type' => Table::TYPE_DATE
                    ]
                );
            }
        }

        $installer->endSetup();
    }
}
