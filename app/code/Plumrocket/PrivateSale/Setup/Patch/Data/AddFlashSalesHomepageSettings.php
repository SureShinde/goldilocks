<?php
/**
 * @package     Plumrocket_PrivateSale
 * @copyright   Copyright (c) 2022 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Setup\Patch\Data;

use Magento\Cms\Model\Page as CmsPage;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * @since 5.1.0
 */
class AddFlashSalesHomepageSettings implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $pageFactory;

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Framework\App\State $state
    ) {
        try {
            $state->setAreaCode('adminhtml');
        } catch (LocalizedException $e) { // phpcs:ignore
        }

        $this->moduleDataSetup = $moduleDataSetup;
        $this->pageFactory = $pageFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $this->pageFactory->create()
            ->setData(
                [
                    CmsPage::IS_ACTIVE   => false,
                    CmsPage::IDENTIFIER  => 'flash-sales-homepage',
                    CmsPage::TITLE       => 'Private Sales Homepage',
                    CmsPage::CONTENT     => $this->getHomepageContent(),
                    CmsPage::PAGE_LAYOUT => '1column'

                ]
            )->save();

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Retrieve homepage xml
     *
     * @return string
     */
    private function getHomepageContent(): string
    {
        return '<div class="shops-holder pps-container endingsoon">' .
            '{{widget type="Plumrocket\PrivateSale\Block\Event\Widget\Active" exclude_ending_soon="1" template="Plumrocket_PrivateSale::homepage/event/group.phtml"}} ' .
            '{{widget type="Plumrocket\PrivateSale\Block\Homepage\Endingsoon" block_title="Ending Soon" template="Plumrocket_PrivateSale::homepage/event/default.phtml"}} ' .
            '{{widget type="Plumrocket\PrivateSale\Block\Homepage\Comingsoon" block_title="Coming Soon" template="Plumrocket_PrivateSale::homepage/event/default.phtml"}}' .
            '</div>';
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
