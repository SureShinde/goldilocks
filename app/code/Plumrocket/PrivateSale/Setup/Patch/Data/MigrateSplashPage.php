<?php
/**
 * @package     Plumrocket_PrivateSale
 * @copyright   Copyright (c) 2022 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * @since 5.1.0
 */
class MigrateSplashPage implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var \Plumrocket\PrivateSale\Model\Data\MigrationFactory
     */
    private $migrationDataFactory;

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface                  $moduleDataSetup
     * @param \Plumrocket\PrivateSale\Model\Data\MigrationFactory                $migrationDataFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Plumrocket\PrivateSale\Model\Data\MigrationFactory $migrationDataFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->migrationDataFactory = $migrationDataFactory;
    }

    /**
     * @inheritdoc
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $migrationData = $this->migrationDataFactory->create();
        $migrationData->migrationSplashPageProcess();

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
