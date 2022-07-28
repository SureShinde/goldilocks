<?php
namespace Magenest\FbChatbot\Logger;

use Magento\Framework\Logger\Handler\Base;

class Handler extends Base
{
    protected $fileName = '/var/log/magenest/fb_chatbot.log';
    protected $loggerType = \Monolog\Logger::DEBUG;
}
