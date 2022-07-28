<?php
/**
 * @package     Plumrocket_PrivateSale
 * @copyright   Copyright (c) 2022 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Setup\Patch\Data;

use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\BlockRepository;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * @since 5.1.0
 */
class CreateTopBannersBlock implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * @var BlockRepository
     */
    private $blockRepository;

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     * @param BlockFactory $blockFactory
     * @param BlockRepository $blockRepository
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        BlockFactory $blockFactory,
        BlockRepository $blockRepository,
        \Magento\Framework\App\State $state
    ) {
        try {
            $state->setAreaCode('adminhtml');
        } catch (LocalizedException $e) { // phpcs:ignore
        }

        $this->moduleDataSetup = $moduleDataSetup;
        $this->blockFactory = $blockFactory;
        $this->blockRepository = $blockRepository;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $blocks = [
            [
                'title' => 'Private Sale Banner №1',
                'identifier' => 'prprivate_sale_banner_1',
                'stores' => [0],
                'is_active' => 1,
                'content' => '<div class="prslide-wrap" style="background-color: #000;">
                    <div class="prslide-content">
                        <div class="prslide-item">
                            <p class="prslide-text">Sale Ends Soon! 20% OFF Everything</p>
                            <a class="prslide-link {{prprivatesale-popup-login-class}}"
                               href="{{prprivatesale-event-url}}"
                               data-form="{{prprivatesale-popup-login-prams}}">SHOP NOW</a>
                        </div>
                    </div>
                 </div>'
            ],
            [
                'title' => 'Private Sale Banner №2',
                'identifier' => 'prprivate_sale_banner_2',
                'stores' => [0],
                'is_active' => 1,
                'content' => '<div class="prslide-wrap prslide-wrap-two" style="background-image: linear-gradient(180deg, #E3F7FD 0%, #D1F1FF 100%);">
                    <div class="prslide-content">
                        <div class="prslide-item">
                            <div class="prslide-description">
                                <img src="{{view url="Plumrocket_PrivateSale::images/topbanners/slide2-image.png"}}" alt="Banner image" width="144" height="37">
                                <p class="prslide-text" style="color: #1c4f9a;">Sport Bags: Last chance to GET 20% OFF all items!</p>
                            </div>
                            <a class="prslide-link {{prprivatesale-popup-login-class}}"
                               href="{{prprivatesale-event-url}}"
                               data-form="{{prprivatesale-popup-login-prams}}">SHOP NOW</a>
                        </div>
                    </div>
                </div>'
            ],
            [
                'title' => 'Private Sale Banner №3',
                'identifier' => 'prprivate_sale_banner_3',
                'stores' => [0],
                'is_active' => 1,
                'content' => '<div class="prslide-wrap prslide-wrap-three" style="background-image: url(\'{{view url="Plumrocket_PrivateSale::images/topbanners/banner3-bg.jpg"}}\');background-repeat:no-repeat;background-size:cover;background-position:center;">
                    <div class="prslide-content">
                        <div class="prslide-item">
                            <p class="prslide-text">Don\'t wait. You\'ll miss out on the sale of the year</p>
                            <a class="prslide-link {{prprivatesale-popup-login-class}}"
                               href="{{prprivatesale-event-url}}"
                               data-form="{{prprivatesale-popup-login-prams}}">SHOP NOW</a>
                        </div>
                    </div>
                </div>'
            ]
        ];

        try {
            foreach ($blocks as $block) {
                $blockData = $this->blockFactory->create(['data' => $block]);
                $this->blockRepository->save($blockData);
            }
        } catch (\Exception $e) {// phpcs:ignore
        }

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
