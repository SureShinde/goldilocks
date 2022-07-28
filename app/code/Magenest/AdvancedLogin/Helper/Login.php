<?php

namespace Magenest\AdvancedLogin\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

/**
 * Class Login
 *
 * @package Magenest\AdvancedLogin\Helper
 */
class Login extends AbstractHelper
{
    const MOBILE_PHONE_NUMBER_ATTRIBUTE_CODE = 'telephone';

    const REGEX_MOBILE_NUMBER = '/^(0)[0-9]{9}$/';

    const REGEX_EMAIL = '/^[_a-z0-9-+]+(\.[_a-z0-9-+]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i';

    protected $customerCollection;

    /**
     * Login constructor.
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollection
     * @param Context $context
     */
    public function __construct(
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollection,
        Context $context
    ) {
        $this->customerCollection = $customerCollection;
        parent::__construct($context);
    }

    /**
     * @param string $mobile
     * @return \Magento\Customer\Model\Customer|\Magento\Framework\DataObject
     */
    public function getCustomerByMobile($mobile)
    {
        $mobile = $this->fixMobileNumber($mobile);
        return $this->customerCollection->create()->addFieldToFilter(self::MOBILE_PHONE_NUMBER_ATTRIBUTE_CODE, $mobile)->getFirstItem();
    }

    /**
     * @param string $phone
     * @return string
     */
    public function fixMobileNumber($phone)
    {
        $phone = preg_replace('/\s+/', '', $phone);
        $phone = preg_replace('/\+/', '', $phone);
        $phone = preg_replace('/^[84]{2}/', '0', $phone);
        $phone = preg_replace('/^0+/', '0', $phone);
        return $phone;
    }
}
