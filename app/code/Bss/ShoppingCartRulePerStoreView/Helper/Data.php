<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_PushNotification
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ShoppingCartRulePerStoreView\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    /**
     * @var \Magento\Framework\Convert\DataObject
     */
    protected $dataObject;

    /**
     * Data constructor
     *
     * @param Context $context
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Convert\DataObject $dataObject
    ) {
        parent::__construct($context);
        $this->dataObject = $dataObject;
    }

    /**
     * @return \Magento\Framework\Convert\DataObject
     */
    public function returnDataObject()
    {
        return $this->dataObject;
    }

    /**
     * @return int
     */
    public function returnCouponSpecific()
    {
        return \Magento\SalesRule\Model\Rule::COUPON_TYPE_SPECIFIC;
    }

    /**
     * @return string
     */
    public function returnDateFormat()
    {
        return \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT;
    }

    /**
     * @return int
     */
    public function returnDateFormatShort()
    {
        return \IntlDateFormatter::SHORT;
    }
}
