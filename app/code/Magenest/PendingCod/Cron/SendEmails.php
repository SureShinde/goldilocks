<?php
namespace Magenest\PendingCod\Cron;

use Magenest\PendingCod\Model\EmailSenderHandler;

/**
 * Class SendEmails
 * @package Magenest\PendingCod\Cron
 */
class SendEmails
{
    /**
     * Global configuration storage.
     *
     * @var EmailSenderHandler
     */
    protected $emailSenderHandler;

    /**
     * @param EmailSenderHandler $emailSenderHandler
     */
    public function __construct(EmailSenderHandler $emailSenderHandler)
    {
        $this->emailSenderHandler = $emailSenderHandler;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $this->emailSenderHandler->sendEmails();
    }
}
