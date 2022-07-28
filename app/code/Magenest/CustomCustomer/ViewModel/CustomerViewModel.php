<?php

namespace Magenest\CustomCustomer\ViewModel;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Block\ArgumentInterface;


class CustomerViewModel implements ArgumentInterface
{
    /**
     * @var Session
     */
    protected $sessionCustomer;

    /**
     * @param Session $sessionCustomer
     */
    public function __construct(
        Session $sessionCustomer
    )
    {
        $this->sessionCustomer = $sessionCustomer;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getName()
    {
        if ($this->sessionCustomer->isLoggedIn()) {
            $customer = $this->sessionCustomer->getCustomer();
            return $customer->getName();
        } else {
            return '';
        }
    }
}
