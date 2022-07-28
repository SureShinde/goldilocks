<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\FbChatbot\Observer\Order;

use Magenest\FbChatbot\Model\Button;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteRepository;
use Magenest\FbChatbot\Model\Bot;
use Magenest\FbChatbot\Model\MessageBuilder;
use Magenest\FbChatbot\Helper\Data;

class UpdateStatus implements ObserverInterface
{
    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;
    /**
     * @var Bot
     */
    protected $bot;
    /**
     * @var MessageBuilder
     */
    protected $messageBuilder;
    /**
     * @var Data
     */
    protected $helperData;
    /**
     * UpdateStatus constructor.
     * @param QuoteRepository $quoteRepository
     * @param Bot $bot
     * @param MessageBuilder $messageBuilder
     * @param Data $helperData
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        Bot $bot,
        MessageBuilder $messageBuilder,
        Data $helperData
        )
    {
        $this->quoteRepository     = $quoteRepository;
        $this->bot                 = $bot;
        $this->messageBuilder      = $messageBuilder;
        $this->helperData          = $helperData;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if(!empty($order->getOrigData('status')) && $order->getOrigData('status') != $order->getStatus()) {
            $this->sendNotifyToMessenger($order);
        }
    }

    public function sendNotifyToMessenger($order) {
        $senderId = $this->getSenderId($order->getQuoteId());
        if(!empty($senderId)) {
            $title = __("Your order #%1 has changed status as %2", $order->getIncrementId(), $order->getStatus())->__toString();
            $url = $this->helperData->getStoreManage()->getStore()->getBaseUrl() . "sales/order/view/order_id/{$order->getId()}";
            $buttons[] = ['type' => Button::BUTTON_URL, 'title' => __('View Detail')->getText(), 'url' => $url];
            $message = $this->messageBuilder->createButtonTemplate($title, $buttons);
            $this->bot->sendMessage($senderId, $message);
        }
    }

    /**
     * @param $quoteId
     * @return string
     */
    public function getSenderId($quoteId): string
    {
        try {
            $quote = $this->quoteRepository->get($quoteId);
            if(!empty($quote->getSenderId())) {
                return $quote->getSenderId();
            }
        } catch (NoSuchEntityException $e) {
            return '';
        }
        return '';
    }

}
