<?php
/**
 * @package     Plumrocket_PrivateSale
 * @copyright   Copyright (c) 2022 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Setup\Patch\Schema;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateEavTables implements SchemaPatchInterface, PatchVersionInterface
{
    /**
     * @var SchemaSetupInterface
     */
    private $schemaSetup;

    /**
     * @var \Plumrocket\PrivateSale\Setup\EavTablesSetupFactory
     */
    private $eavTablesSetupFactory;

    /**
     * @param SchemaSetupInterface                                  $schemaSetup
     * @param \Plumrocket\PrivateSale\Setup\EavTablesSetupFactory   $eavTablesSetupFactory
     */
    public function __construct(
        SchemaSetupInterface $schemaSetup,
        \Plumrocket\PrivateSale\Setup\EavTablesSetupFactory $eavTablesSetupFactory
    ) {
        $this->schemaSetup = $schemaSetup;
        $this->eavTablesSetupFactory = $eavTablesSetupFactory;
    }

    /**
     * @inheritdoc
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function apply()
    {
        $this->schemaSetup->startSetup();

        $eventEntity = \Plumrocket\PrivateSale\Model\Event::ENTITY;

        //Create Value Tables
        /** @var \Plumrocket\PrivateSale\Setup\EavTablesSetup $eavTablesSetup */
        $eavTablesSetup = $this->eavTablesSetupFactory->create(['setup' => $this->schemaSetup]);
        $eavTablesSetup->createEavTables($eventEntity);

        $this->schemaSetup->endSetup();
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
