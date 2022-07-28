<?php

namespace Magenest\AdvancedLogin\Helper;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;

class PhoneHelper
{
    /**
     * @var CollectionFactory
     */
    private $customerCollectionFactory;

    public function __construct(
        CollectionFactory $customerCollectionFactory
    ) {
        $this->customerCollectionFactory = $customerCollectionFactory;
    }

    public function checkPhoneExist($telephone)
    {
        $customerCollection = $this->customerCollectionFactory->create();
        return $customerCollection->addAttributeToFilter("telephone", $telephone)->count() !== 0;
    }

    /**
     * @param $telephone
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerIdByPhoneNumber($telephone)
    {
        $customerCollection = $this->customerCollectionFactory->create();
        return $customerCollection->addAttributeToFilter("telephone", $telephone)->getFirstItem()->getId();
    }
}
