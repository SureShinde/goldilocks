<?php

declare(strict_types=1);

namespace Amasty\PreOrderAnalytic\Model;

use Amasty\PreOrderAnalytic\Model\Price\Convert as PriceConvert;
use Amasty\PreOrderAnalytic\Model\ResourceModel\LoadRevenuePreorder;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class GetRevenuePreorder
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var LoadRevenuePreorder
     */
    private $loadRevenuePreorder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var PriceConvert
     */
    private $priceConvert;

    public function __construct(
        ConfigProvider $configProvider,
        LoadRevenuePreorder $loadRevenuePreorder,
        StoreManagerInterface $storeManager,
        PriceConvert $priceConvert
    ) {
        $this->configProvider = $configProvider;
        $this->loadRevenuePreorder = $loadRevenuePreorder;
        $this->storeManager = $storeManager;
        $this->priceConvert = $priceConvert;
    }

    public function execute(array $params = []): string
    {
        $params['status'] = ['in' => $this->configProvider->getRevenueOrderStatuses()];

        $revenue = 0.0;
        $revenuePreorderRows = $this->loadRevenuePreorder->execute($params);
        foreach ($revenuePreorderRows as $revenuePreorderRow) {
            $revenue += $this->priceConvert->execute(
                (float) $revenuePreorderRow[LoadRevenuePreorder::REVENUE_COLUMN],
                $revenuePreorderRow[LoadRevenuePreorder::CURRENCY_CODE_COLUMN]
            );
        }

        return $this->storeManager->getStore(Store::DEFAULT_STORE_ID)->getBaseCurrency()->format(
            $revenue,
            [],
            false
        );
    }
}
