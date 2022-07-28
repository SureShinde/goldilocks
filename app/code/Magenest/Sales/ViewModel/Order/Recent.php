<?php

namespace Magenest\Sales\ViewModel\Order;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use \Magento\Directory\Model\CountryFactory;


class Recent implements ArgumentInterface
{
    /**
     * @var CountryFactory
     */
    protected $countryFactory;

    /**
     * @param CountryFactory $countryFactory
     */
    public function __construct(
        CountryFactory $countryFactory
    )
    {
        $this->countryFactory = $countryFactory;
    }

    /**
     * @param $countryCode
     * @return string
     */
    public function getCountryName($countryCode){
        $country = $this->countryFactory->create()->loadByCode($countryCode);
        return $country->getName();
    }

}
