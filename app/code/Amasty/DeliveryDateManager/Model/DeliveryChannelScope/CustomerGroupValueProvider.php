<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryChannelScope;

use Amasty\DeliveryDateManager\Api\ScopeValueProviderInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Data\Group;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Sales\Api\Data\OrderInterface;

class CustomerGroupValueProvider implements ScopeValueProviderInterface
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    public function __construct(
        Session $session,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->session = $session;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getValue(): string
    {
        return (string)$this->session->getCustomerGroupId();
    }

    /**
     * @param AddressInterface|Address $address
     * @return string
     */
    public function extractValueFromAddress(AddressInterface $address): string
    {
        return $this->getCustomerGroupId((int)$address->getCustomerId());
    }

    /**
     * @param OrderInterface $order
     * @return string
     */
    public function extractValueFromOrder(OrderInterface $order): string
    {
        return (string)$order->getCustomerGroupId();
    }

    /**
     * @param int $customerId
     * @return string
     */
    private function getCustomerGroupId(int $customerId): string
    {
        $customerGroupId = (string)Group::NOT_LOGGED_IN_ID;

        if (!$customerId) {
            return $customerGroupId;
        }

        try {
            $customer = $this->customerRepository->getById($customerId);
            return (string)$customer->getGroupId();
        } catch (LocalizedException $e) {
            return $customerGroupId;
        }
    }
}
