<?php

declare(strict_types=1);

namespace Amasty\Preorder\Plugin\Email\Model\Template;

use Amasty\Preorder\Model\ConfigProvider;
use Amasty\Preorder\Model\Order\GetWarning;
use Amasty\Preorder\Model\Order\IsPreorder;
use Magento\Email\Model\Template as NativeTemplate;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\OrderIdentity;

class AddNoteToOrderEmail
{
    /**
     * @var OrderIdentity
     */
    private $orderIdentity;

    /**
     * @var GetWarning
     */
    private $getWarning;

    /**
     * @var IsPreorder
     */
    private $isPreorder;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        OrderIdentity $orderIdentity,
        IsPreorder $isPreorder,
        GetWarning $getWarning,
        ConfigProvider $configProvider
    ) {
        $this->orderIdentity = $orderIdentity;
        $this->getWarning = $getWarning;
        $this->isPreorder = $isPreorder;
        $this->configProvider = $configProvider;
    }

    public function afterGetProcessedTemplate(NativeTemplate $subject, string $result, array $variables = []): string
    {
        $order = null;

        if (isset($variables['order']) && $variables['order'] instanceof Order) {
            $order = $variables['order'];
        }

        if ($order
            && $this->configProvider->isEnabled()
            && $this->configProvider->isWarningInEmail()
        ) {
            $templateCode = null;

            if ($this->isPreorder->execute($order)) {
                $templateCode = $order->getCustomerIsGuest()
                    ? $this->orderIdentity->getGuestTemplateId()
                    : $this->orderIdentity->getTemplateId();
            }

            if ($templateCode && $templateCode == $subject->getId()) {
                $warningBlock = sprintf(
                    '<p style="padding: 10px; background-color: #f5f5f5; font-weight: 400;">%s</p>',
                    $this->getWarning->execute((int) $order->getId())
                );
                $result = preg_replace(
                    '@<[^>]*class=["\'][^"\']*greeting[^>]*>@',
                    $warningBlock . '$0',
                    $result,
                    1
                );
            }
        }

        return $result;
    }
}
