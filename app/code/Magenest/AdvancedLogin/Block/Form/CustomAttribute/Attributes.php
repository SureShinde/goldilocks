<?php

namespace Magenest\AdvancedLogin\Block\Form\CustomAttribute;

use Magenest\AdvancedLogin\Model\ConfigProvider;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;

class Attributes extends Template
{
    /** @var ConfigProvider */
    protected $_configProvider;

    protected $_telephone;

    /** @var CustomerRepositoryInterface */
    protected $_customerRepository;

    /**
     * Attributes constructor.
     * @param Template\Context $context
     * @param ConfigProvider $configProvider
     * @param CustomerRepositoryInterface $customerRepository
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        ConfigProvider $configProvider,
        CustomerRepositoryInterface $customerRepository,
        array $data = []
    ) {
        $this->_configProvider = $configProvider;
        $this->_customerRepository = $customerRepository;
        parent::__construct($context, $data);
    }

    /**
     * @param $telephone
     */
    public function setDataAttributes($telephone)
    {
        $this->_telephone = $telephone ? $telephone : '';
    }

    /**
     * @param $id
     */
    public function getDataAttributeCustomer($id)
    {
        try {
            $customer = $this->_customerRepository->getById($id);
            $telephone = $customer->getCustomAttribute('telephone');
            $this->_telephone = $telephone ? $telephone->getValue() : '';
        } catch (NoSuchEntityException $e) {
            $this->_logger->critical($e);
        } catch (LocalizedException $e) {
            $this->_logger->critical($e);
        }
    }

    /**
     * @return mixed
     */
    public function getTelephone()
    {
        return $this->_telephone;
    }

    /**
     * @return int
     */
    public function getActiveTelephone()
    {
        return $this->_configProvider->isTelephoneLoginEnable();
    }

    public function isInCustomerUpdatePage()
    {
        if ($this->_request->getFullActionName() == "customer_account_edit") {
            return true;
        }
        return  false;
    }
}
