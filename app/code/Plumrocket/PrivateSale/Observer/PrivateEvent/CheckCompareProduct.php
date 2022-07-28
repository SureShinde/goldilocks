<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket Private Sales and Flash Sales
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Observer\PrivateEvent;

class CheckCompareProduct extends AbstractPermission
{
    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (! $this->config->isModuleEnabled()) {
            return;
        }

        $controller = $observer->getEvent()->getData('controller_action');
        $productId = $controller->getRequest()->getParam('product');

        try {
            $product = $this->productRepository->getById($productId);
            $event = $this->eventCatalog->getEventForProduct($product);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return;
        }

        if ($event && $event->isEventPrivate()) {
            $redirectUrl = $event->getPrivateSaleLandingPageUrl();
            $controller->getResponse()->setRedirect($redirectUrl, self::REDIRECT_CODE);
            $controller->getResponse()->sendResponse();
            $controller->getRequest()->setDispatched(true);
            $controller->setFlag('', \Magento\Framework\App\ActionInterface::FLAG_NO_DISPATCH, true);
        }
    }
}
