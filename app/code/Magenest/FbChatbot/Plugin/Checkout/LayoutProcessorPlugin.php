<?php
namespace Magenest\FbChatbot\Plugin\Checkout;

use Magento\Checkout\Model\Session;
use Magenest\FbChatbot\Helper\Data as FbChatbotHelper;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
/**
 * Class LayoutProcessorPlugin
 * @package Magenest\FbChatbot\Plugin\Checkout
 */
class LayoutProcessorPlugin
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var FbChatbotHelper
     */
    protected $dataHelper;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $_cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * LayoutProcessorPlugin constructor.
     * @param Session $session
     * @param FbChatbotHelper $dataHelper
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     */
    public function __construct(
        Session $session,
        FbChatbotHelper $dataHelper,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory
    )
    {
        $this->checkoutSession = $session;
        $this->dataHelper = $dataHelper;
        $this->_cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
    }

    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array  $jsLayout
    ) {
        $quote = $this->checkoutSession->getQuote();
        $storedMessage = $this->dataHelper->unserialize($quote->getStoredMessage());
        try {
            if(is_array($storedMessage)) {
                $shippingAddressFieldset = $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                ['shippingAddress']['children']['shipping-address-fieldset']['children'];
                $shippingAddressFieldset['firstname']['value'] = $storedMessage['firstName'];
                $shippingAddressFieldset['lastname']['value'] = $storedMessage['lastName'];
                $shippingAddressFieldset['street']['children'][0]['value'] = $storedMessage['street'];
                $shippingAddressFieldset['city']['value'] = $storedMessage['city'];
                $shippingAddressFieldset['postcode']['value'] = $storedMessage['postCode'];
                $shippingAddressFieldset['country_id']['value'] = $storedMessage['country']['value'];
                $shippingAddressFieldset['telephone']['value'] = $storedMessage['telephone'];
                if(isset($storedMessage['country']['is_region_required'])){
                    $shippingAddressFieldset['region_id']['value'] = $storedMessage['region']['value'];
                }else{
                    $shippingAddressFieldset['region'] = $this->setRegionField($shippingAddressFieldset['region_id'], $storedMessage['region']);
                }
                // to reload directory_data
                $this->setPublicCookie('section_data_clean', 'need_reload');

                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                ['shippingAddress']['children']['shipping-address-fieldset']['children'] = $shippingAddressFieldset;
            }
        }catch (\Throwable $exception){
            $this->dataHelper->getLogger()->error('Fill shipping address failed '. $exception->getMessage());
        }

        return $jsLayout;
    }

    /**
     * set region field
     * @param $region
     * @param $value
     * @return mixed
     */
    public function setRegionField($region, $value)
    {
        unset($region['config']['customEntry']);
        unset($region['deps']);
        unset($region['imports']);
        $region['component'] = 'Magento_Ui/js/form/element/abstract';
        $region['config']['elementTmpl'] = 'ui/form/element/input';
        $region['dataScope'] = 'shippingAddress.region';
        $region['validation']['required-entry'] = false;
        $region['value'] = $value;
        $region['visible'] = false;
        return $region;
    }

    /**
     * set cookie section
     * @param $cookieName
     * @param $value
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function setPublicCookie($cookieName, $value) {
        $metadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setDuration(6400)
            ->setSecure(true)
            ->setPath('/');

        $this->_cookieManager->setPublicCookie(
            $cookieName,
            $value,
            $metadata
        );
    }
}
