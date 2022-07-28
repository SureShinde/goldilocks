<?php
/**
 * @author iPay88 Inc. <support@ipay88.com.my>
 * @package iPay88\ThirdpartyIntegration\Magento
 * @Description: Using for Magento 2.x and Philippines Only.
 */
namespace Ipay88\Ipay88\Model\Config\Source\Order\Status;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Config\Source\Order\Status;

/**
 * Order Status source model
 */
class Pendingpayment extends Status
{
    /**
     * @var string[]
     */
    protected $_stateStatuses = [Order::STATE_PENDING_PAYMENT];
}
