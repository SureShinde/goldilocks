<?php
namespace Magenest\FbChatbot\Observer;

use Magenest\FbChatbot\Helper\Data;
use Magenest\FbChatbot\Model\Bot;
use Magenest\FbChatbot\Model\Message;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SetupGreetingApi implements ObserverInterface {

    /**
     * @var Data
     */
    private $helper;
    /**
     * @var Bot
     */
    private $bot;

    /**
     * SetupGreetingApi constructor.
     * @param Data $helper
     * @param Bot $bot
     */
    public function __construct(
        Data $helper,
        Bot $bot
    )
    {
        $this->helper = $helper;
        $this->bot = $bot;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $changedPaths = $observer->getChangedPaths();
        if (in_array(Data::XML_PATH_MESSAGE_GREETING,$changedPaths)){
            $this->bot->setupGreetingMessage($this->helper->getGreetingMessage());
        }
        if(in_array(Data::XML_PATH_CONNECT_ACCESS_TOKEN,$changedPaths)){
            $this->bot->setupGettingStarted(Message::GET_STARTED_MESSAGE);
        }
    }
}
