<?php
/**
 * @package     Plumrocket_PrivateSale
 * @copyright   Copyright (c) 2022 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * @since 5.1.0
 */
class MigratePrivateSalesAttributes implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var \Plumrocket\PrivateSale\Model\Data\MigrationFactory
     */
    private $migrationDataFactory;

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface                  $moduleDataSetup
     * @param \Magento\Eav\Setup\EavSetupFactory                                 $eavSetupFactory
     * @param \Plumrocket\PrivateSale\Model\Data\MigrationFactory                $migrationDataFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        \Plumrocket\PrivateSale\Model\Data\MigrationFactory $migrationDataFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->migrationDataFactory = $migrationDataFactory;
    }

    /**
     * @inheritdoc
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $allAttributes = [
            \Magento\Catalog\Model\Category::ENTITY => [
                'privatesale_email_image',
                'privatesale_date_start',
                'privatesale_date_end',
                'privatesale_before_event_start',
                'privatesale_event_end',
                'privatesale_private_event',
                'privatesale_restrict_cgroup',
                'privatesale_event_landing'
            ],
            \Magento\Catalog\Model\Product::ENTITY  => [
                'privatesale_date_start',
                'privatesale_date_end',
                'privatesale_before_event_start',
                'privatesale_event_end',
                'privatesale_private_event',
                'privatesale_restrict_cgroup',
                'privatesale_event_landing'
            ]
        ];

        $migrationData = $this->migrationDataFactory->create();
        $migrationData->migrationEventsProcess();

        foreach ($allAttributes as $typeId => $attributeCodes) {
            foreach ($attributeCodes as $attributeCode) {
                $eavSetup->removeAttribute($typeId, $attributeCode);
            }
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public function revert(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [
            InstallEventEntities::class
        ];
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public static function getVersion(): string
    {
        return '5.0.0';
    }
}
