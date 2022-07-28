<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magenest\FbChatbot\Model\HumanSupport;

use Magenest\FbChatbot\Helper\Data;
use Magenest\FbChatbot\Model\Bot;

class Consumer
{
    const TOPIC_NAME = 'chatbot.sendMail';
    /**
     * @var Bot
     */
    private $bot;
    /**
     * @var Data
     */
    private $helper;

    /**
     * Consumer constructor.
     * @param Bot $bot
     * @param Data $helper
     */
    public function __construct(
        Bot $bot,
        Data $helper
    )
    {
        $this->bot = $bot;
        $this->helper = $helper;
    }

    public function process(string $senderId)
    {
        $userData = $this->bot->getUserData($senderId);
        $fanpageInfo = $this->bot->getFanpageInformation();
        $userMailbox = $this->bot->getUserMailbox($senderId);
        if (isset($userData) && isset($fanpageInfo) && isset($userMailbox['data'])){
            $this->helper->sendEmail($userData,$fanpageInfo,$userMailbox);
        }
    }
}
