<?php

namespace Magenest\FbChatbot\Setup\Patch\Data;

use Magenest\FbChatbot\Api\MessageRepositoryInterface;
use Magenest\FbChatbot\Model\Button;
use Magenest\FbChatbot\Model\Message;
use Magenest\FbChatbot\Model\MessageFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 */
class InsertMessageCreateOrder implements DataPatchInterface
{
    const MESSAGE_TYPE = "message_type";
    const TEXT         = 'text';
    const PRODUCT_NAME = 'product_name';
    const LABEL             = 'label';
    const DEFAULT_TYPING_TIME = '1';
    const CREATE_ORDER_CODE = 'create_order';
    const OPTIONS           = 'options';
    const RECORD_ID         = 'record_id';
    const INCLUDE_BUTTON    = 'include_button';
    const VALUES            = 'values';
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    protected $moduleDataSetup;
    /**
     * @var MessageFactory
     */
    private $messageFactory;

    /**
     * @var MessageRepositoryInterface
     */
    private $messageRepository;

    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        MessageFactory $messageFactory,
        MessageRepositoryInterface $messageRepository
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->messageFactory = $messageFactory;
        $this->messageRepository = $messageRepository;
    }

    public static function getDependencies()
    {
        return [
            InsertMessageData::class,
            InsertButtonData::class,
            InsertMenuData::class,
            InsertNewButtonData::class,
            InsertNewMessageData::class
        ];
    }

    public function getAliases()
    {
        return [];
    }

    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $createOrderMessage = array(
            Message::ACTIVE => Message::MESSAGE_STATUS_ACTIVE,
            Message::NAME => __('Create Order'),
            Message::TYPING => Message::MESSAGE_TYPING_ACTIVE,
            Message::DESCRIPTION => __('This message will create an ordering process on the bot'),
            Message::TYPING_TIME => self::DEFAULT_TYPING_TIME,
            Message::MESSAGE_CODE => self::CREATE_ORDER_CODE,
            Message::MESSAGE_TYPES => array(
                self::OPTIONS => array(
                    array(
                        self::RECORD_ID => "1",
                        self::MESSAGE_TYPE => Message::MESSAGE_TYPE_CREATE_ORDER,
                        Message::TITLE => __("Create Order"),
                        self::INCLUDE_BUTTON => "0",
                    )
                )
            )
        );
        $message = $this->messageFactory->create();
        $message->setData($createOrderMessage);
        $this->messageRepository->save($message);

        $this->moduleDataSetup->getConnection()->endSetup();
    }
}