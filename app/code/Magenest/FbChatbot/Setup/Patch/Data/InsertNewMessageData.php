<?php

namespace Magenest\FbChatbot\Setup\Patch\Data;

use Magenest\FbChatbot\Api\MessageRepositoryInterface;
use Magenest\FbChatbot\Model\Button;
use Magenest\FbChatbot\Model\Message;
use Magenest\FbChatbot\Model\MessageFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 */
class InsertNewMessageData implements DataPatchInterface
{
    const MESSAGE_TYPE = "message_type";
    const TEXT         = 'text';
    const CATEGORY_LEVEL = "category_level";
    const BUTTONS = 'buttons';
    const BUTTON_TYPE = 'button_type';
    const MESSAGE_ID_PAYLOAD = 'message_id_payload';
    const PRODUCT_NAME = 'product_name';
    const LABEL = 'label';
    const DEFAULT_TYPING_TIME = '1';
    const STORE_NAME = '{$storeName}';
    const STORE_ADDRESS = '{$storeAddress}';
    const STORE_TELEPHONE = '{$storeTelephone}';
    const BASE_URL = '{$baseUrl}';
    const BUTTON_LABEL = 'button_label';
    const BUTTON_ACTION = 'button_action';
    const OPTIONS =     'options';
    const RECORD_ID = 'record_id';
    const INCLUDE_BUTTON = 'include_button';
    const VALUES = 'values';

    const GET_STARTED_CODE = 'get_started';
    const DEFAULT_MESSAGE_CODE = 'default_message';
    const SHOW_PRODUCT_BUTTONS_CODE = 'show_product_buttons';
    const SHOW_CATEGORY_CODE = "show_category";
    const CONTINUE_WITH_BOT_CODE = "continue_with_bot";
    const REQUEST_HUMAN_SUPPORT_CODE = "request_human_support";
    const VIEW_ADDRESS_CODE = "view_address";
    const ABOUT_US_CODE = "about_us";
    const WHAT_WOULD_YOU_LIKE_TO_DO_CODE = "what_would_you_like_to_do";
    const MY_ORDERS_CODE = "my_orders";
    const MY_WISHLIST_CODE = "my_wishlist";

    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var MessageFactory
     */
    private $messageFactory;

    /**
     * @var MessageRepositoryInterface
     */
    private $messageRepository;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json|mixed|null
     */
    protected $serializer;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        MessageFactory $messageFactory,
        MessageRepositoryInterface $messageRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        /**
         * If before, we pass $setup as argument in install/upgrade function, from now we start
         * inject it with DI. If you want to use setup, you can inject it, with the same way as here
         */
        $this->moduleDataSetup = $moduleDataSetup;
        $this->messageFactory = $messageFactory;
        $this->messageRepository = $messageRepository;
        $this->storeManager = $storeManager;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
    }

    public static function getDependencies()
    {
        return [
            InsertMessageData::class,
            InsertButtonData::class,
            InsertMenuData::class,
            InsertNewButtonData::class
        ];
    }

    /**
     * {@inheritdoc}
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $data [] = array(
            Message::ACTIVE => Message::MESSAGE_STATUS_ACTIVE,
            Message::NAME => __(Message::DEFAULT_MESSAGE),
            Message::TYPING => Message::MESSAGE_TYPING_ACTIVE,
            Message::TYPING_TIME => self::DEFAULT_TYPING_TIME,
            Message::MESSAGE_CODE => self::DEFAULT_MESSAGE_CODE,
            Message::MESSAGE_TYPES => array(
                self::OPTIONS => array(
                    array(
                        Message::TITLE => __('Sorry, but I don’t recognize'),
                        self::MESSAGE_TYPE => Message::MESSAGE_TYPE_TEXT,
                        self::TEXT => __("Sorry, but I don't recognize"),
                        self::INCLUDE_BUTTON => "0",
                    ),
                    array(
                        self::RECORD_ID => "1",
                        Message::TITLE => __('How can we help you?'),
                        self::INCLUDE_BUTTON => "1",
                        self::MESSAGE_TYPE => Message::MESSAGE_TYPE_TEXT,
                        self::TEXT => __("How can we help you?"),
                        self::VALUES => array(
                            array(
                                self::RECORD_ID => "0",
                                self::BUTTON_TYPE => Button::BUTTON_TYPE_TEXT,
                                self::BUTTON_LABEL => __("About Us"),
                                self::BUTTON_ACTION => "7"
                            ),
                            array(
                                self::RECORD_ID => "1",
                                self::BUTTON_TYPE => Button::BUTTON_TYPE_TEXT,
                                self::BUTTON_LABEL => __("Show Category"),
                                self::BUTTON_ACTION => "3"
                            )
                        )
                    )
                )
            )
        );

        $data [] = array(
            Message::ACTIVE => Message::MESSAGE_STATUS_ACTIVE,
            Message::NAME => __('Get Started'),
            Message::TYPING => Message::MESSAGE_TYPING_ACTIVE,
            Message::TYPING_TIME => self::DEFAULT_TYPING_TIME,
            Message::MESSAGE_CODE => self::GET_STARTED_CODE,
            Message::MESSAGE_TYPES => array(
                self::OPTIONS => array(
                    array(
                        Message::TITLE => __('Welcome to '. self::STORE_NAME),
                        self::MESSAGE_TYPE => Message::MESSAGE_TYPE_TEXT,
                        self::TEXT => __('Welcome to '. self::STORE_NAME),
                        self::INCLUDE_BUTTON => "0",
                    ),
                    array(
                        self::RECORD_ID => "1",
                        Message::TITLE => __('What we can do to help you today'),
                        self::INCLUDE_BUTTON => "1",
                        self::MESSAGE_TYPE => Message::MESSAGE_TYPE_TEXT,
                        self::TEXT => __('What we can do to help you today'),
                        self::VALUES => array(
                            array(
                                self::RECORD_ID => "0",
                                self::BUTTON_TYPE => Button::BUTTON_TYPE_URL,
                                self::BUTTON_LABEL => __('Shop now'),
                                self::BUTTON_ACTION => self::BASE_URL
                            ),
                            array(
                                self::RECORD_ID => "1",
                                self::BUTTON_TYPE => Button::BUTTON_TYPE_TEXT,
                                self::BUTTON_LABEL => __("Show Category"),
                                self::BUTTON_ACTION => "3"

                            )
                        )
                    )
                )
            )
        );

        $data [] = array(
            Message::ACTIVE => Message::MESSAGE_STATUS_ACTIVE,
            Message::NAME => __('Show Categories'),
            Message::TYPING => Message::MESSAGE_TYPING_ACTIVE,
            Message::TYPING_TIME => self::DEFAULT_TYPING_TIME,
            Message::MESSAGE_CODE => self::SHOW_CATEGORY_CODE,
            Message::MESSAGE_TYPES => array(
                self::OPTIONS => array(
                    array(
                        self::RECORD_ID => "1",
                        Message::TITLE => __('Show Categories'),
                        self::INCLUDE_BUTTON => "1",
                        self::CATEGORY_LEVEL => '2',
                        self::MESSAGE_TYPE => Message::MESSAGE_TYPE_CATEGORIES,
                        self::VALUES => array(
                            array(
                                self::RECORD_ID => "0",
                                self::BUTTON_TYPE => Button::BUTTON_TYPE_ACTION,
                                self::BUTTON_LABEL => __('View category detail'),
                                self::BUTTON_ACTION => "3"
                            ),
                            array(
                                self::RECORD_ID => "1",
                                self::BUTTON_TYPE => Button::BUTTON_TYPE_ACTION,
                                self::BUTTON_LABEL => __("Show products"),
                                self::BUTTON_ACTION => "1"
                            )
                        )
                    )
                )
            )
        );

        $data [] = array(
            Message::ACTIVE => Message::MESSAGE_STATUS_ACTIVE,
            Message::NAME => __('Continue with Bot'),
            Message::TYPING => Message::MESSAGE_TYPING_ACTIVE,
            Message::TYPING_TIME => self::DEFAULT_TYPING_TIME,
            Message::MESSAGE_CODE => self::CONTINUE_WITH_BOT_CODE,
            Message::MESSAGE_TYPES => array(
                self::OPTIONS => array(
                    array(
                        Message::TITLE => __('Okay welcome back.'),
                        self::MESSAGE_TYPE => Message::MESSAGE_TYPE_TEXT,
                        self::TEXT => __('Okay welcome back.'),
                        self::INCLUDE_BUTTON => "0",
                    )
                )
            )
        );

        $data [] = array(
            Message::ACTIVE => Message::MESSAGE_STATUS_ACTIVE,
            Message::NAME => __('Request Human Support'),
            Message::TYPING => Message::MESSAGE_TYPING_ACTIVE,
            Message::TYPING_TIME => self::DEFAULT_TYPING_TIME,
            Message::MESSAGE_CODE => self::REQUEST_HUMAN_SUPPORT_CODE,
            Message::MESSAGE_TYPES => array(
                self::OPTIONS => array(
                    array(
                        self::RECORD_ID => "1",
                        Message::TITLE => __('Our agent will be here soon'),
                        self::TEXT => __('Our agent will be here soon'),
                        self::INCLUDE_BUTTON => "1",
                        self::MESSAGE_TYPE => Message::MESSAGE_TYPE_TEXT,
                        self::VALUES => array(
                            array(
                                self::RECORD_ID => "0",
                                self::BUTTON_TYPE => Button::BUTTON_TYPE_TEXT,
                                self::BUTTON_LABEL => __('Continue with Bot'),
                                self::BUTTON_ACTION => "4"
                            )
                        )
                    )
                )
            )
        );

        $data [] = array(
            Message::ACTIVE => Message::MESSAGE_STATUS_ACTIVE,
            Message::NAME => __('View Address'),
            Message::TYPING => Message::MESSAGE_TYPING_ACTIVE,
            Message::TYPING_TIME => self::DEFAULT_TYPING_TIME,
            Message::MESSAGE_CODE => self::VIEW_ADDRESS_CODE,
            Message::MESSAGE_TYPES => array(
                self::OPTIONS => array(
                    array(
                        Message::TITLE => __('View Address'),
                        self::MESSAGE_TYPE => Message::MESSAGE_TYPE_TEXT,
                        self::TEXT => __('You can visit the store at '. self::STORE_ADDRESS),
                        self::INCLUDE_BUTTON => "0",
                    )
                )
            )
        );

        $data [] = array(
            Message::ACTIVE => Message::MESSAGE_STATUS_ACTIVE,
            Message::NAME => __('About Us'),
            Message::TYPING => Message::MESSAGE_TYPING_ACTIVE,
            Message::TYPING_TIME => self::DEFAULT_TYPING_TIME,
            Message::MESSAGE_CODE => self::ABOUT_US_CODE,
            Message::MESSAGE_TYPES => array(
                self::OPTIONS => array(
                    array(
                        self::RECORD_ID => "1",
                        Message::TITLE => __('We are here to help when you have questions on any products.'),
                        self::TEXT =>__('We are here to help when you have questions on any products.'),
                        self::INCLUDE_BUTTON => "1",
                        self::MESSAGE_TYPE => Message::MESSAGE_TYPE_TEXT,
                        self::VALUES => array(
                            array(
                                self::RECORD_ID => "0",
                                self::BUTTON_TYPE => Button::BUTTON_TYPE_TELEPHONE,
                                self::BUTTON_LABEL => __('Call'),
                                self::BUTTON_ACTION => self::STORE_TELEPHONE
                            ),
                            array(
                                self::RECORD_ID => "1",
                                self::BUTTON_TYPE => Button::BUTTON_TYPE_TEXT,
                                self::BUTTON_LABEL => __('View Address'),
                                self::BUTTON_ACTION => "6"
                            )
                        )
                    )
                )
            )
        );

        $data [] = array(
            Message::ACTIVE => Message::MESSAGE_STATUS_ACTIVE,
            Message::NAME => __('What would you like to do ?'),
            Message::TYPING => Message::MESSAGE_TYPING_ACTIVE,
            Message::TYPING_TIME => self::DEFAULT_TYPING_TIME,
            Message::MESSAGE_CODE => self::WHAT_WOULD_YOU_LIKE_TO_DO_CODE,
            Message::MESSAGE_TYPES => array(
                self::OPTIONS => array(
                    array(
                        self::RECORD_ID => "1",
                        Message::TITLE => __('What would you like to do ?'),
                        self::TEXT => __('What would you like to do ?'),
                        self::INCLUDE_BUTTON => "1",
                        self::MESSAGE_TYPE => Message::MESSAGE_TYPE_TEXT,
                        self::VALUES => array(
                            array(
                                self::RECORD_ID => "0",
                                self::BUTTON_TYPE => Button::BUTTON_TYPE_TEXT,
                                self::BUTTON_LABEL => __('Show Category'),
                                self::BUTTON_ACTION => "3"
                            ),
                            array(
                                self::RECORD_ID => "1",
                                self::BUTTON_TYPE => Button::BUTTON_TYPE_ACTION,
                                self::BUTTON_LABEL => __('Modify cart & Checkout'),
                                self::BUTTON_ACTION => "5"
                            )
                        )
                    )
                )
            )
        );

        $data [] = array(
            Message::ACTIVE => Message::MESSAGE_STATUS_ACTIVE,
            Message::NAME => __('Show Product Buttons'),
            Message::TYPING => Message::MESSAGE_TYPING_ACTIVE,
            Message::DESCRIPTION => __('This message is for retrieving the buttons when showing the product from category'),
            Message::TYPING_TIME => self::DEFAULT_TYPING_TIME,
            Message::MESSAGE_CODE => self::SHOW_PRODUCT_BUTTONS_CODE,
            Message::MESSAGE_TYPES => array(
                self::OPTIONS => array(
                    array(
                        self::RECORD_ID => "1",
                        self::MESSAGE_TYPE => Message::MESSAGE_TYPE_PRODUCT,
                        Message::TITLE => __("Show Product Buttons"),
                        self::PRODUCT_NAME => "",
                        self::INCLUDE_BUTTON => "1",
                        self::VALUES => array(
                            array(
                                self::RECORD_ID => "0",
                                self::BUTTON_TYPE => Button::BUTTON_TYPE_ACTION,
                                self::BUTTON_LABEL => __('View Product Detail'),
                                self::BUTTON_ACTION => "2"
                            ),
                            array(
                                self::RECORD_ID => "1",
                                self::BUTTON_TYPE => Button::BUTTON_TYPE_ACTION,
                                self::BUTTON_LABEL => __('Add To Cart'),
                                self::BUTTON_ACTION => "4"
                            )
                        )
                    )
                )
            )
        );

        $data [] = array(
            Message::ACTIVE => Message::MESSAGE_STATUS_ACTIVE,
            Message::NAME => __('My Orders'),
            Message::TYPING => Message::MESSAGE_TYPING_ACTIVE,
            Message::DESCRIPTION => __('This message will display the last 5 orders of the customer'),
            Message::TYPING_TIME => self::DEFAULT_TYPING_TIME,
            Message::MESSAGE_CODE => self::MY_ORDERS_CODE,
            Message::MESSAGE_TYPES => array(
                self::OPTIONS => array(
                    array(
                        self::RECORD_ID => "1",
                        self::MESSAGE_TYPE => Message::MESSAGE_TYPE_ORDER,
                        Message::TITLE => __("My Order"),
                        self::INCLUDE_BUTTON => "1",
                        self::VALUES => array(
                            array(
                                self::RECORD_ID => "0",
                                self::BUTTON_TYPE => Button::BUTTON_TYPE_ACTION,
                                self::BUTTON_LABEL => __('Write A Product Review'),
                                self::BUTTON_ACTION => "6"
                            ),
                            array(
                                self::RECORD_ID => "1",
                                self::BUTTON_TYPE => Button::BUTTON_TYPE_ACTION,
                                self::BUTTON_LABEL => __('View Order Detail'),
                                self::BUTTON_ACTION => "7"
                            )
                        )
                    )
                )
            )
        );
        $data [] = array(
            Message::ACTIVE => Message::MESSAGE_STATUS_ACTIVE,
            Message::NAME => __('My Wishlist'),
            Message::TYPING => Message::MESSAGE_TYPING_ACTIVE,
            Message::DESCRIPTION => __('This message will display the last 5 wishlist of the customer'),
            Message::TYPING_TIME => self::DEFAULT_TYPING_TIME,
            Message::MESSAGE_CODE => self::MY_WISHLIST_CODE,
            Message::MESSAGE_TYPES => array(
                self::OPTIONS => array(
                    array(
                        self::RECORD_ID => "1",
                        self::MESSAGE_TYPE => Message::MESSAGE_TYPE_WISHLIST,
                        Message::TITLE => "My Wishlist",
                        self::INCLUDE_BUTTON => "1",
                        self::VALUES => array(
                            array(
                                self::RECORD_ID => "0",
                                self::BUTTON_TYPE => Button::BUTTON_TYPE_ACTION,
                                self::BUTTON_LABEL => __('View Product Detail'),
                                self::BUTTON_ACTION => "2"
                            ),
                            array(
                                self::RECORD_ID => "1",
                                self::BUTTON_TYPE => Button::BUTTON_TYPE_ACTION,
                                self::BUTTON_LABEL => __('Add To Cart'),
                                self::BUTTON_ACTION => "4"
                            )
                        )
                    )
                )
            )
        );

        foreach ($data as $key => $item) {
            if($key < 9) {
                $tableMessage = $this->moduleDataSetup->getTable('magenest_chatbot_message');
                $item[Message::MESSAGE_TYPES] = $this->serializer->serialize($item[Message::MESSAGE_TYPES]);
                $this->moduleDataSetup->getConnection()->insertOnDuplicate($tableMessage,['message_id' => $key + 1, 'message_types' => $item[Message::MESSAGE_TYPES], 'code' => $item[Message::MESSAGE_CODE]],['message_types', 'code']);
            }else{
                $message = $this->messageFactory->create();
                $message->setData($item);
                $this->messageRepository->save($message);
            }

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
