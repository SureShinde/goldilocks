<?php

namespace Magenest\FbChatbot\Setup\Patch\Data;

use Magenest\FbChatbot\Api\ButtonRepositoryInterface;
use Magenest\FbChatbot\Model\Button;
use Magenest\FbChatbot\Model\ButtonFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 */
class InsertButtonData implements DataPatchInterface
{
    const NAME = "name";
    const DESCRIPTION = "description";
    const BUTTON_TYPE = "button_type";
    const TITLE = "title";
    /**
     * @var ButtonFactory
     */
    protected $buttonFactory;
    /**
     * @var ButtonRepositoryInterface
     */
    protected $buttonRepository;
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * InsertButtonData constructor.
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     * @param ButtonFactory $buttonFactory
     * @param ButtonRepositoryInterface $buttonRepository
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        ButtonFactory $buttonFactory,
        ButtonRepositoryInterface $buttonRepository
    ) {
        /**
         * If before, we pass $setup as argument in install/upgrade function, from now we start
         * inject it with DI. If you want to use setup, you can inject it, with the same way as here
         */
        $this->moduleDataSetup = $moduleDataSetup;
        $this->buttonFactory = $buttonFactory;
        $this->buttonRepository = $buttonRepository;
    }

    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $data [] = [Button::CODE => 'show_products', self::BUTTON_TYPE => Button::BUTTON_ACTION_CATEGORY, self::TITLE => __('Show Products')];
        $data [] = [Button::CODE => 'view_product_detail', self::BUTTON_TYPE => Button::BUTTON_ACTION_PRODUCT, self::TITLE => __('View Product Detail')];
        $data [] = [Button::CODE => 'view_category_detail', self::BUTTON_TYPE => Button::BUTTON_ACTION_CATEGORY, self::TITLE => __('View Category Detail')];
        $data [] = [Button::CODE => 'add_to_cart', self::BUTTON_TYPE => Button::BUTTON_ACTION_PRODUCT, self::TITLE => __('Add To Cart')];
        $data [] = [Button::CODE => 'modify_cart_and_checkout', self::BUTTON_TYPE => Button::BUTTON_ACTION_GENERAL, self::TITLE => __('Modify Cart & Checkout')];

        foreach ($data as $item) {
            $button = $this->buttonFactory->create();
            $button->setData($item);
            $this->buttonRepository->save($button);
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        /**
         * This internal Magento method, that means that some patches with time can change their names,
         * but changing name should not affect installation process, that's why if we will change name of the patch
         * we will add alias here
         */
        return [];
    }
}
