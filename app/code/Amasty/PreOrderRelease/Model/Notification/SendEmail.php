<?php

declare(strict_types=1);

namespace Amasty\PreOrderRelease\Model\Notification;

use Amasty\PreOrderRelease\Model\ConfigProvider;
use Magento\Framework\App\Area;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Url as UrlBuilder;

class SendEmail
{
    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var UrlBuilder
     */
    private $urlBuilder;

    public function __construct(
        TransportBuilder $transportBuilder,
        ConfigProvider $configProvider,
        UrlBuilder $urlBuilder
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->configProvider = $configProvider;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param string $customerEmail
     * @param int $storeId
     * @param string $productName
     * @return void
     */
    public function execute(string $customerEmail, int $storeId, string $productName): void
    {
        $this->transportBuilder->setTemplateIdentifier(
            $this->configProvider->getReleaseEmailTemplate($storeId)
        )->setTemplateOptions(
            ['area' => Area::AREA_FRONTEND, 'store' => $storeId]
        )->setTemplateVars([
            'product_name' => $productName,
            'orders_url' => $this->urlBuilder->getUrl('sales/order/history', [
                '_scope' => $storeId,
                '_scope_to_url' => true,
                '_nosid' => true
            ])
        ])->setFromByScope(
            $this->configProvider->getReleaseEmailSender($storeId),
            $storeId
        )->addTo(
            $customerEmail
        );
        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();
    }
}
