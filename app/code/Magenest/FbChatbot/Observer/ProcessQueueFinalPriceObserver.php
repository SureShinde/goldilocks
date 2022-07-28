<?php

declare(strict_types=1);

namespace Magenest\FbChatbot\Observer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\Observer;

class ProcessQueueFinalPriceObserver extends \Magento\CatalogRule\Observer\ProcessFrontFinalPriceObserver {

    /**
     * get finalPrice for process creation order
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        try {
            $appState =  ObjectManager::getInstance()->get(\Magento\Framework\App\State::class);
            if($appState->getAreaCode() == 'global')
                parent::execute($observer);
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
        }
    }
}
