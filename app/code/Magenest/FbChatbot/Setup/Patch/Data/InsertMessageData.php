<?php

namespace Magenest\FbChatbot\Setup\Patch\Data;

use Magenest\FbChatbot\Api\MessageRepositoryInterface;
use Magenest\FbChatbot\Model\Button;
use Magenest\FbChatbot\Model\Message;
use Magenest\FbChatbot\Model\MessageFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 */
class InsertMessageData implements DataPatchInterface
{
    const TYPE = "type";
    const CATEGORY_LEVEL = "category_level";
    const BUTTONS = 'buttons';
    const BUTTON_TYPE = 'button_type';
    const MESSAGE_ID_PAYLOAD = 'message_id_payload';
    const BUTTON_ACTION_TYPE = 'button_action_type';
    const LABEL = 'label';
    const URL_OR_TELEPHONE = 'url_or_telephone';
    const DEFAULT_TYPING_TIME = '1';
    const STORE_NAME = '{$storeName}';
    const STORE_ADDRESS = '{$storeAddress}';
    const STORE_TELEPHONE = '{$storeTelephone}';
    const BASE_URL = '{$baseUrl}';

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
     * InsertMessageData constructor.
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     * @param MessageFactory $messageFactory
     * @param MessageRepositoryInterface $messageRepository
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        MessageFactory $messageFactory,
        MessageRepositoryInterface $messageRepository
    ) {
        /**
         * If before, we pass $setup as argument in install/upgrade function, from now we start
         * inject it with DI. If you want to use setup, you can inject it, with the same way as here
         */
        $this->moduleDataSetup = $moduleDataSetup;
        $this->messageFactory = $messageFactory;
        $this->messageRepository = $messageRepository;
    }

    public static function getDependencies()
    {
        return [InsertButtonData::class];
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
            Message::MESSAGE_TYPES => array(
                array(
                    Message::TITLE => __('Sorry, but I don’t recognize'),
                    self::TYPE => Message::MESSAGE_TYPE_TEXT,
                ),
                array(
                    Message::TITLE => __('How can we help you?'),
                    self::TYPE => Message::MESSAGE_TYPE_QUICK_REPLY,
                    self::BUTTONS => array(
                        array(self::BUTTON_TYPE => Button::BUTTON_TYPE_TEXT,self::MESSAGE_ID_PAYLOAD => '7'),
                        array(self::BUTTON_TYPE => Button::BUTTON_TYPE_TEXT,self::MESSAGE_ID_PAYLOAD => '3')
                    )
                )
            )
        );

        $data [] = array(
            Message::ACTIVE => Message::MESSAGE_STATUS_ACTIVE,
            Message::NAME => __('Get Started'),
            Message::TYPING => Message::MESSAGE_TYPING_ACTIVE,
            Message::TYPING_TIME => self::DEFAULT_TYPING_TIME,
            Message::MESSAGE_TYPES => array(
                array(
                    Message::TITLE => __('Welcome to '. self::STORE_NAME),
                    self::TYPE => Message::MESSAGE_TYPE_TEXT
                ),
                array(
                    Message::TITLE => __('What we can do to help you today'),
                    self::TYPE => Message::MESSAGE_TYPE_BUTTON_AND_TEXT,
                    self::BUTTONS => array(
                        array(self::BUTTON_TYPE => Button::BUTTON_TYPE_URL,self::LABEL => __('Shop now'), self::URL_OR_TELEPHONE => self::BASE_URL),
                        array(self::BUTTON_TYPE => Button::BUTTON_TYPE_TEXT, self::MESSAGE_ID_PAYLOAD => '3')
                    )
                )
            )
        );
        $data [] = array(
            Message::ACTIVE => Message::MESSAGE_STATUS_ACTIVE,
            Message::NAME => __('Show Categories'),
            Message::TYPING => Message::MESSAGE_TYPING_ACTIVE,
            Message::TYPING_TIME => self::DEFAULT_TYPING_TIME,
            Message::MESSAGE_TYPES => array(
                array(
                    self::TYPE => Message::MESSAGE_TYPE_CATEGORIES,
                    self::CATEGORY_LEVEL => '2',
                    self::BUTTONS => array(
                        array(self::BUTTON_TYPE => Button::BUTTON_TYPE_ACTION,self::BUTTON_ACTION_TYPE => '3'),
                        array(self::BUTTON_TYPE => Button::BUTTON_TYPE_ACTION,self::BUTTON_ACTION_TYPE => '1')
                    )
                )
            )
        );

        $data [] = array(
            Message::ACTIVE => Message::MESSAGE_STATUS_ACTIVE,
            Message::NAME => __('Continue with Bot'),
            Message::TYPING => Message::MESSAGE_TYPING_ACTIVE,
            Message::TYPING_TIME => self::DEFAULT_TYPING_TIME,
            Message::MESSAGE_TYPES => array(
                array(
                    Message::TITLE => __('Okay welcome back.'),
                    self::TYPE => Message::MESSAGE_TYPE_TEXT
                )
            )
        );

        $data [] = array(
            Message::ACTIVE => Message::MESSAGE_STATUS_ACTIVE,
            Message::NAME => __('Request Human Support'),
            Message::TYPING => Message::MESSAGE_TYPING_ACTIVE,
            Message::TYPING_TIME => self::DEFAULT_TYPING_TIME,
            Message::MESSAGE_TYPES => array(
                array(
                    Message::TITLE => __('Our agent will be here soon'),
                    self::TYPE => Message::MESSAGE_TYPE_BUTTON_AND_TEXT,
                    self::BUTTONS => array(
                        array(self::BUTTON_TYPE => Button::BUTTON_TYPE_TEXT, self::MESSAGE_ID_PAYLOAD => '4')
                    )

                )
            )
        );

        $data [] = array(
            Message::ACTIVE => Message::MESSAGE_STATUS_ACTIVE,
            Message::NAME => __('View Address'),
            Message::TYPING => Message::MESSAGE_TYPING_ACTIVE,
            Message::TYPING_TIME => self::DEFAULT_TYPING_TIME,
            Message::MESSAGE_TYPES => array(
                array(
                    Message::TITLE => __('You can visit the store at '. self::STORE_ADDRESS),
                    self::TYPE => Message::MESSAGE_TYPE_TEXT,
                )
            )
        );

        $data [] = array(
            Message::ACTIVE => Message::MESSAGE_STATUS_ACTIVE,
            Message::NAME => __('About Us'),
            Message::TYPING => Message::MESSAGE_TYPING_ACTIVE,
            Message::TYPING_TIME => self::DEFAULT_TYPING_TIME,
            Message::MESSAGE_TYPES => array(
                array(
                    Message::TITLE => __('We are here to help when you have questions on any products.'),
                    self::TYPE => Message::MESSAGE_TYPE_BUTTON_AND_TEXT,
                    self::BUTTONS => array(
                        array(self::BUTTON_TYPE => Button::BUTTON_TYPE_TELEPHONE,
                            self::LABEL => __('Call'),
                            self::URL_OR_TELEPHONE => self::STORE_TELEPHONE),
                        array(self::BUTTON_TYPE => Button::BUTTON_TYPE_TEXT, self::MESSAGE_ID_PAYLOAD => '6')
                    )

                )
            )
        );

        $data [] = array(
            Message::ACTIVE => Message::MESSAGE_STATUS_ACTIVE,
            Message::NAME => __('What would you like to do ?'),
            Message::TYPING => Message::MESSAGE_TYPING_ACTIVE,
            Message::TYPING_TIME => self::DEFAULT_TYPING_TIME,
            Message::MESSAGE_TYPES => array(
                array(
                    Message::TITLE => __('What would you like to do ?'),
                    self::TYPE => Message::MESSAGE_TYPE_BUTTON_AND_TEXT,
                    self::BUTTONS => array(
                        array(self::BUTTON_TYPE => Button::BUTTON_TYPE_TEXT, self::MESSAGE_ID_PAYLOAD => "3"),
                        array(self::BUTTON_TYPE => Button::BUTTON_TYPE_ACTION, self::BUTTON_ACTION_TYPE => "5")
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
            Message::MESSAGE_TYPES => array(
                array(
                    self::TYPE => Message::MESSAGE_TYPE_PRODUCT,
                    self::BUTTONS => array(
                        array(self::BUTTON_TYPE => Button::BUTTON_TYPE_ACTION, self::BUTTON_ACTION_TYPE => "2"),
                        array(self::BUTTON_TYPE => Button::BUTTON_TYPE_ACTION, self::BUTTON_ACTION_TYPE => "4")
                    )

                )
            )
        );

        foreach ($data as $item) {
            $message = $this->messageFactory->create();
            $message->setData($item);
            $this->messageRepository->save($message);
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
