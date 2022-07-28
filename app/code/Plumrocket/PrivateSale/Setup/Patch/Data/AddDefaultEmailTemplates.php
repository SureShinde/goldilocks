<?php
/**
 * @package     Plumrocket_PrivateSale
 * @copyright   Copyright (c) 2022 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Plumrocket\PrivateSale\Setup\EmailTemplateData;

/**
 * @since 5.1.0
 */
class AddDefaultEmailTemplates implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var \Plumrocket\PrivateSale\Model\Emailtemplate
     */
    private $emailTemplate;

    /**
     * @var EmailTemplateData
     */
    protected $emailTemplateData;

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     * @param \Plumrocket\PrivateSale\Model\EmailtemplateFactory $emailTemplate
     * @param EmailTemplateData $emailTemplateData
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Plumrocket\PrivateSale\Model\EmailtemplateFactory $emailTemplate,
        EmailTemplateData $emailTemplateData
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->emailTemplate = $emailTemplate;
        $this->emailTemplateData = $emailTemplateData;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $this->emailTemplate->create()
            ->setData(
                [
                    'name' => 'One Event in Row',
                    'template' => $this->emailTemplateData->getDefaultEmailTemplate(),
                    'list_template' => $this->emailTemplateData->getListTemplateOne(),
                    'list_template_date_format' => 'm/d/Y',
                    'list_layout' => '1'
                ]
            )->save();

        $this->emailTemplate->create()
            ->setData(
                [
                    'name' => 'Two Event in Row',
                    'template' => $this->emailTemplateData->getDefaultEmailTemplate(),
                    'list_template' => $this->emailTemplateData->getListTemplateTwo(),
                    'list_template_date_format' => 'm/d/Y',
                    'list_layout' => '2'
                ]
            )->save();

        $this->emailTemplate->create()
            ->setData(
                [
                    'name' => 'Three Event in Row',
                    'template' => $this->emailTemplateData->getDefaultEmailTemplate(),
                    'list_template' => $this->emailTemplateData->getListTemplateThree(),
                    'list_template_date_format' => 'm/d/Y',
                    'list_layout' => '3'
                ]
            )->save();

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [];
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
