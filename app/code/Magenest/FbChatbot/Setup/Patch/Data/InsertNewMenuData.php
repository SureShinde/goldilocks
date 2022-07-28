<?php

namespace Magenest\FbChatbot\Setup\Patch\Data;

use Magenest\FbChatbot\Api\MenuRepositoryInterface;
use Magenest\FbChatbot\Api\MessageRepositoryInterface;
use Magenest\FbChatbot\Model\Button;
use Magenest\FbChatbot\Model\MenuFactory;
use Magenest\FbChatbot\Model\ResourceModel\Menu\CollectionFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 */
class InsertNewMenuData implements DataPatchInterface
{
    const ACTIVE = 'is_active';
    const NAME = "name";
    const DESCRIPTION = "description";
    const MESSAGE_ID = "message_id";
    /**
     * @var MenuFactory
     */
    protected $menuFactory;

    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CollectionFactory
     */
    protected $menuColFactory;

    /**
     * @var MessageRepositoryInterface
     */
    protected $messageRepository;

    /**
     * @var MenuRepositoryInterface
     */
    protected $menuRepository;
    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * InsertMenuData constructor.
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     * @param MenuFactory $menuFactory
     * @param MenuRepositoryInterface $menuRepository
     * @param CollectionFactory $menuColFactory
     * @param MessageRepositoryInterface $messageRepository
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        MenuFactory $menuFactory,
        MenuRepositoryInterface $menuRepository,
        CollectionFactory $menuColFactory,
        MessageRepositoryInterface $messageRepository,
        \Magento\Framework\App\State $state
    ) {
        /**
         * If before, we pass $setup as argument in install/upgrade function, from now we start
         * inject it with DI. If you want to use setup, you can inject it, with the same way as here
         */
        $this->moduleDataSetup = $moduleDataSetup;
        $this->menuFactory = $menuFactory;
        $this->menuRepository = $menuRepository;
        $this->menuColFactory = $menuColFactory;
        $this->messageRepository = $messageRepository;
        $this->state = $state;
    }

    public static function getDependencies()
    {
        return [
            InsertMessageData::class,
            InsertButtonData::class,
            InsertMenuData::class,
            InsertNewMessageData::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $data [] = [self::ACTIVE => '1',self::NAME => __('My Orders'), self::MESSAGE_ID => $this->messageRepository->getByCode('my_orders')->getId()];
        $data [] = [self::ACTIVE => '1',self::NAME => __('My Wishlist'), self::DESCRIPTION => __("Please send us your registered email on our website"), self::MESSAGE_ID => $this->messageRepository->getByCode('my_wishlist')->getId()];

        foreach ($data as $item) {
            $menu = $this->menuFactory->create();
            $menu->setData($item);
            $this->menuRepository->save($menu);
        }
        $menus = null;
        foreach ($data as $item){
            $message = $this->messageRepository->getById($item[self::MESSAGE_ID]);
            $menus [] = ['type' => Button::BUTTON_POSTBACK, 'title' => $message->getName(),'payload' => $message->getName()];
        }
        // check area code not set
        try {
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        }catch (\Magento\Framework\Exception\LocalizedException $e) {
            //do nothing
        }
        $bot = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magenest\FbChatbot\Model\Bot::class);
        $bot->setupPersistentMenu($menus, false);
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
