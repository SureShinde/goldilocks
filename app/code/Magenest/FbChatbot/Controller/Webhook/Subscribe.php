<?php

namespace Magenest\FbChatbot\Controller\Webhook;

use Magenest\FbChatbot\Helper\Data;
use Magenest\FbChatbot\Helper\SessionHelper;
use Magenest\FbChatbot\Model\Bot;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultInterface;

class Subscribe extends Action implements CsrfAwareActionInterface
{
    const HUB_MODE = "hub_mode";
    const HUB_VERIFY_TOKEN = "hub_verify_token";
    const HUB_CHALLENGE = "hub_challenge";

	/**
	 * @var Raw
	 */
	public $resultRawFactory;

	/**
	 * @var Data
	 */
	protected $helper;

	/**
	 * @var Bot
	 */
	protected $bot;

	/**
	 * @var SessionHelper
	 */
	protected $sessionHelper;

    /**
     * Subscribe constructor.
     * @param Context $context
     * @param RawFactory $resultRawFactory
     * @param Data $helper
     * @param Bot $bot
     * @param SessionHelper $sessionHelper
     */
	public function __construct(
		Context $context,
		RawFactory $resultRawFactory,
		Data $helper,
		Bot $bot,
		SessionHelper $sessionHelper
	) {
		$this->resultRawFactory = $resultRawFactory;
		$this->helper           = $helper;
		$this->bot              = $bot;
		$this->sessionHelper    = $sessionHelper;
		parent::__construct($context);
	}

    /**
     * @return ResponseInterface|ResultInterface
     */
	public function execute()
	{
        $resultRaw = $this->resultRawFactory->create()
            ->setContents('Access denied')
            ->setHttpResponseCode(403);
		if ($this->getRequest()->isPost()) {
		    if ($this->helper->isEnableModule()){
                $this->dataHandler();
            }else{
		        $this->helper->getLogger()->critical(__('Module is disabled'));
            }
            $resultRaw->setContents("Success")->setHttpResponseCode(200);
		} else {
		    $this->subscribeWebhook($resultRaw);
		}
		return $resultRaw;
	}

	private function dataHandler(){
        $inputData = $this->helper->unserialize(file_get_contents('php://input'));
        $this->helper->getLogger()->critical('User Message', $inputData);
        if (isset($inputData['object']) && $inputData['object'] === 'page') {
            try {
                $messagingObject = $inputData['entry'][0]['messaging'][0];
                $senderId        = $messagingObject['sender']['id'];
                $this->bot->setQuote($this->sessionHelper->getRecipientQuote($senderId));
                if ($message = $this->bot->getMessageFromUser($messagingObject)){
                    $this->bot->handleMessage($senderId, $message,isset($messagingObject['message']['quick_reply']));
                }
            }catch (\Throwable $e){
                $this->helper->getLogger()->error("An error occurred while process: ". $e->getMessage());
            }
        }
    }

    /**
     * Subscribe webhooks integration
     * @param Raw $resultRaw
     */
	private function subscribeWebhook(Raw $resultRaw){
        $verifyToken = $this->helper->getVerificationToken();
        $mode        = $this->getRequest()->getParam(self::HUB_MODE);
        $token       = $this->getRequest()->getParam(self::HUB_VERIFY_TOKEN);
        $challenge   = $this->getRequest()->getParam(self::HUB_CHALLENGE);
        if ($mode && $token) {
            if ($mode === 'subscribe' && $token === $verifyToken) {
                $this->helper->getLogger()->info('WEBHOOK_VERIFIED');
                $resultRaw->setContents($challenge)->setHttpResponseCode(200);
            }
        }
    }

	/**
	 * @inheritDoc
	 */
	public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
	{
		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function validateForCsrf(RequestInterface $request): ?bool
	{
		return true;
	}
}
