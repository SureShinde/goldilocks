<?php

declare(strict_types=1);

namespace Amasty\PreOrderAnalytic\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Config as OrderConfig;

class ConfigProvider extends \Amasty\Preorder\Model\ConfigProvider
{
    const PENDING_ORDER_STATUSES = 'analytic/pending_order_status';
    const REVENUE_ORDER_STATUSES = 'analytic/revenue_order_status';

    /**
     * @var OrderConfig
     */
    private $orderConfig;

    public function __construct(OrderConfig $orderConfig, ScopeConfigInterface $scopeConfig)
    {
        parent::__construct($scopeConfig);
        $this->orderConfig = $orderConfig;
    }

    public function getPendingOrderStatuses(): array
    {
        $value = $this->getValue(self::PENDING_ORDER_STATUSES);
        if ($value === null) {
            $result = $this->getStatuses([
                Order::STATE_NEW,
                Order::STATE_PENDING_PAYMENT,
                Order::STATE_PROCESSING,
                Order::STATE_PAYMENT_REVIEW
            ]);
        } else {
            $result = explode(',', $value);
        }

        return $result;
    }

    public function getRevenueOrderStatuses(): array
    {
        $value = $this->getValue(self::REVENUE_ORDER_STATUSES);
        if ($value === null) {
            $result = $this->getStatuses([Order::STATE_COMPLETE]);
        } else {
            $result = explode(',', $value);
        }

        return $result;
    }

    /**
     * @param string[] $states
     * @return string[]
     */
    private function getStatuses(array $states): array
    {
        $result = $this->orderConfig->getStateStatuses($states);
        return array_keys($result);
    }
}
