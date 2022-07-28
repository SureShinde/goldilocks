<?php

namespace Acommerce\SmsIntegration\Helper;

use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const NEW_ORDER = 'new_order';
    const EDIT_ORDER = 'edit_order';
    const READY_PICKUP = 'ready_for_pickup';
    const COMPLETE_ORDER = 'complete_order';

    const XML_ENABLE_SANDBOX_MODE_PATH = 'acommerce_smsintegrarion/otp_setting/enable_sandbox';
    const XML_OTP_TIMEOUT_PATH                            = 'acommerce_smsintegrarion/otp_setting/otp_timeout';
    const XML_OTP_RESEND_TIMEOUT_PATH                            = 'acommerce_smsintegrarion/otp_setting/otp_resend_timeout';
    const XML_OTP_LENGTH_PATH                             = 'acommerce_smsintegrarion/otp_setting/otp_length';
    const XML_OTP_MESSAGE_PATH                            = 'acommerce_smsintegrarion/otp_setting/otp_message';
    const XML_OTP_COUNTRY_NUMBER_CODE_PATH = 'acommerce_smsintegrarion/otp_setting/country_number_code';

    const OTP_RAW_TEXT = "{{var otp}}";
    const OTP_CODE_TEST = '0000';

    private $_scopeConfig;

    public $_context;

    protected $_orderRepository;

    protected $_pricingData;

    protected $_storeManager;

    protected $frontUrlModel;

    private $priceCurrency;
    /**
     * @var \Acommerce\SmsIntegration\Model\Session
     */
    private $smsIntegrationSession;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Pricing\Helper\Data $pricingData,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $frontUrlModel,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Acommerce\SmsIntegration\Model\Session $smsIntegrationSession
    ) {
        $this->_context = $context;
        $this->_scopeConfig = $scopeConfig;
        $this->_orderRepository = $orderRepository;
        $this->_pricingData = $pricingData;
        $this->_storeManager = $storeManager;
        $this->frontUrlModel = $frontUrlModel;
        $this->priceCurrency = $priceCurrency;
        $this->smsIntegrationSession = $smsIntegrationSession;
    }

    public function smsNewOrder($order, $typeMessage)
    {
        $customer_name = $order->getCustomerName();
        $orderIncrementId = $order->getIncrementId();
        $storeId = $order->getStoreId();
        $total = $this->_pricingData->currency($order->getGrandTotal(), true, false);
        $store_name = $order->getStore()->getName();
       /* $pickup_date = $order->getData('checkout_order_pickup');
        $pickup_time = $order->getData('checkout_time_range');*/
        $routeParams['order_id'] = $order->getId();
        $routeParams['_nosid'] = true;
        $order_link = $this->frontUrlModel->getUrl('sales/order/view/', $routeParams);

        $orig_order_id = strpos($order->getIncrementId(), "-") ? substr($order->getIncrementId(), 0, strpos($order->getIncrementId(), "-")) : $order->getIncrementId();
        $orderComment = [];
        foreach ($order->getStatusHistoryCollection() as $status) {
            if ($status->getComment()) {
                $orderComment[] = $status->getComment();
            }
        }

        $templateMessage = $this->getTemplateMessage($typeMessage, $storeId);

        if (strpos($order->getCustomerEmail(), 'smmarkets.shopsm.com') !== false) {
            $order_link = "";
            $templateMessage = str_replace("\n\nView order details here:", "", $templateMessage);
            $templateMessage = str_replace("\r\n\r\nView order details here:", "", $templateMessage);
            $templateMessage = str_replace("\r\rView order details here:", "", $templateMessage);
            //$order_link = str_replace('https://', 'shopsmapp://', $order_link);
        }

        $templateMessage = str_replace('{{var customer_name}}', $customer_name, $templateMessage);
        $templateMessage = str_replace('{{var order_id}}', '#' . $orderIncrementId, $templateMessage);
        $templateMessage = str_replace('{{var order_total}}', $total, $templateMessage);
        $templateMessage = str_replace('{{var store_name}}', $store_name, $templateMessage);
   /*     $templateMessage = str_replace('{{var pickup_date}}', $pickup_date, $templateMessage);
        $templateMessage = str_replace('{{var pickup_time}}', $pickup_time, $templateMessage);*/
        $templateMessage = str_replace('{{var order_link}}', $order_link, $templateMessage);
        $templateMessage = str_replace('{{var orig_order_id}}', $orig_order_id, $templateMessage);
        if (!empty($orderComment)) {
            $templateMessage = str_replace('{{var order_comment}}', $orderComment[0], $templateMessage);
        } else {
            $templateMessage = str_replace('{{var order_comment}}', "", $templateMessage);
        }
        if ($templateMessage) {
            $this->sendSms($order, $templateMessage, $storeId);
        }
    }

    public function smsBooking($order, $deliveryId, $status, $tracking = null)
    {
        $customerName = $order->getCustomerName();
        $orderIncrementId = $order->getIncrementId();
        $templateMessage = $this->getTemplateMessage('booking');
        $storeId = $order->getStoreId();

        $templateMessage = str_replace('{{var customer_name}}', $customerName, $templateMessage);
        $templateMessage = str_replace('{{var order_id}}', $orderIncrementId, $templateMessage);
        $templateMessage = str_replace('{{var booking_id}}', $deliveryId, $templateMessage);
        $templateMessage = str_replace('{{var tracking_utl}}', $tracking, $templateMessage);
        $templateMessage = str_replace('{{var status}}', $status, $templateMessage);
        if ($templateMessage) {
            $this->sendSms($order, $templateMessage, $storeId);
        }
    }

    public function smsInDelivery($order, $deliveryId, $status, $tracking = null)
    {
        $customerName = $order->getCustomerName();
        $orderIncrementId = $order->getIncrementId();
        $storeId = $order->getStoreId();
        $templateMessage = $this->getTemplateMessage('in_delivery', $storeId);
        $grand_total = $this->priceCurrency->convertAndFormat($order->getGrandTotal(), false);

        $is_cod = $order->getPayment()->getMethod() == 'cashondelivery';
        $is_paynamics = $order->getPayment()->getMethod() == 'pnx';

        if ($is_cod) {
            $payment_message = 'Please prepare payment when the driver arrives.';
        } elseif ($is_paynamics) {
            $payment_message = 'Thank you for your payment.';
        } else {
            $payment_message = "";
        }

        $templateMessage = str_replace('{{var customer_name}}', $customerName, $templateMessage);
        $templateMessage = str_replace('{{var order_id}}', $orderIncrementId, $templateMessage);
        $templateMessage = str_replace('{{var booking_id}}', $deliveryId, $templateMessage);
        $templateMessage = str_replace('{{var tracking_utl}}', $tracking, $templateMessage);
        $templateMessage = str_replace('{{var status}}', $status, $templateMessage);
        $templateMessage = str_replace('{{var grand_total}}', $grand_total, $templateMessage);
        $templateMessage = str_replace('{{var payment_message}}', $payment_message, $templateMessage);
        if ($templateMessage) {
            $this->sendSms($order, $templateMessage, $storeId);
        }
    }

    public function smsReadyPickUpOrder($order, $typeMessage, $comment)
    {
        $customer_name = $order->getCustomerName();
        $orderIncrementId = $order->getIncrementId();
        $routeParams['order_id'] = $order->getId();
        $routeParams['_nosid'] = true;
        $storeId = $order->getStoreId();
        $order_link = $this->frontUrlModel->getUrl('sales/order/view/', $routeParams);
        $templateMessage = $this->getTemplateMessage($typeMessage, $storeId);
        $templateMessage = str_replace('{{var customer_name}}', $customer_name, $templateMessage);
        $templateMessage = str_replace('{{var order_id}}', '#' . $orderIncrementId, $templateMessage);
        $templateMessage = str_replace('{{var order_comment}}', $comment, $templateMessage);
        $templateMessage = str_replace('{{var order_link}}', $order_link, $templateMessage);
        if ($templateMessage) {
            $this->sendSms($order, $templateMessage, $storeId);
        }
    }

    public function smsPaymentLink($order, $typeMessage, $payment_link)
    {
        $customer_name = $order->getCustomerName();
        $orderIncrementId = $order->getIncrementId();
        $storeId = $order->getStoreId();
        $total = $this->_pricingData->currency($order->getGrandTotal(), true, false);
        $templateMessage = $this->getTemplateMessage($typeMessage, $storeId);
        $templateMessage = str_replace('{{var customer_name}}', $customer_name, $templateMessage);
        $templateMessage = str_replace('{{var order_id}}', '#' . $orderIncrementId, $templateMessage);
        $templateMessage = str_replace('{{var order_total}}', $total, $templateMessage);
        $templateMessage = str_replace('{{var payment_link}}', $payment_link, $templateMessage);
        if ($templateMessage) {
            $this->sendSms($order, $templateMessage, $storeId, true);
        }
    }

    public function smsCompleteOrder($order, $typeMessage)
    {
        $customer_name = $order->getCustomerName();
        $orderIncrementId = $order->getIncrementId();
        $storeId = $order->getStoreId();
        $templateMessage = $this->getTemplateMessage($typeMessage, $storeId);
        $templateMessage = str_replace('{{var customer_name}}', $customer_name, $templateMessage);
        $templateMessage = str_replace('{{var order_id}}', '#' . $orderIncrementId, $templateMessage);
        if ($templateMessage) {
            $this->sendSms($order, $templateMessage, $storeId);
        }
    }

    public function smsCommentOnOrder($order, $typeMessage, $comment)
    {
        $customer_name = $order->getCustomerName();
        $orderIncrementId = $order->getIncrementId();
        $storeId = $order->getStoreId();
        $templateMessage = $this->getTemplateMessage($typeMessage, $storeId);
        $templateMessage = str_replace('{{var customer_name}}', $customer_name, $templateMessage);
        $templateMessage = str_replace('{{var order_id}}', '#' . $orderIncrementId, $templateMessage);
        $templateMessage = str_replace('{{var order_comment}}', $comment, $templateMessage);
        if ($templateMessage) {
            $useBillingAddress = $order->getShippingMethod() === \Amasty\StorePickupWithLocator\Model\Carrier\Shipping::SHIPPING_NAME;
            $this->sendSms($order, $templateMessage, $storeId, $useBillingAddress);
        }
    }

    public function sendSms($order, $templateMessage, $storeId = null, $useBillingAddress = false)
    {
        $postUrl = $this->getPostUrl($storeId);
        $orderIncrementId = $order->getIncrementId();
        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();

        // creating an object for sending SMS
        $messageId = $orderIncrementId;
        if ($useBillingAddress) {
            $to = $billingAddress->getData('telephone');
        } else {
            $to = $shippingAddress->getData('telephone');
        }
        $countryNumberCode = $this->getCountryNumberCodeConfig();
        if (substr($to, 0, 1) == '0') {
            $to = $countryNumberCode . substr($to, 1);
        } elseif (substr($to, 0, 2) == substr($countryNumberCode, 1)) {
            $to = '+' . $to;
        }

        $from = $this->getSmSFrom($storeId);
        $text = $templateMessage;
        $notifyUrl = $this->_context->getUrlBuilder()->getUrl('smsintegration/index/notify');
        $notifyContentType = 'application/json';
        $callbackData = $this->_context->getUrlBuilder()->getUrl('smsintegration/index/callback');
        $destination = ['messageId' => $messageId, 'to' => $to];
        $message = ["from" => $from,
            'destinations' =>[$destination],
            'text' => $text,
            'notifyUrl' => $notifyUrl,
            'notifyContentType' => $notifyContentType,
            'callbackData' => $callbackData
        ];

        $postData = ['messages' => [$message]];
        //encoding object
        $postDataJson = json_encode($postData);

        $username = $this->getUserName($storeId);
        $password = $this->getPassword($storeId);

        $ch = curl_init();
        $header = ["content-type: application/json", "accept: application/json"];
        // setting options
        curl_setopt($ch, CURLOPT_URL, $postUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataJson);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
    }

    public function getUserName($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->getStoreId();
        }
        $username = $this->_scopeConfig->getValue('acommerce_smsintegrarion/credentials/username', ScopeInterface::SCOPE_STORE, $storeId);

        return $username;
    }

    public function getPassword($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->getStoreId();
        }
        $username = $this->_scopeConfig->getValue('acommerce_smsintegrarion/credentials/password', ScopeInterface::SCOPE_STORE, $storeId);
        return $username;
    }

    public function getTemplateMessage($template, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->getStoreId();
        }
        $path = 'acommerce_smsintegrarion/sms_messages/' . $template;
        return $this->_scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getSmSFrom($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->getStoreId();
        }
        $from = $this->_scopeConfig->getValue('acommerce_smsintegrarion/general/from', ScopeInterface::SCOPE_STORE, $storeId);
        return $from ? $from : 'SmMarkets';
    }

    public function getPostUrl($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->getStoreId();
        }
        $url= $this->_scopeConfig->getValue('acommerce_smsintegrarion/credentials/host', ScopeInterface::SCOPE_STORE, $storeId);
        return $url ? $url : 'https://8d1p1.api.infobip.com/sms/2/text/advanced';
    }

    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * @param $storeId
     * @return bool
     */
    public function getIsSandboxMode($storeId = null): bool
    {
        if (null === $storeId) {
            $storeId = $this->getStoreId();
        }
        return (boolean)$this->_scopeConfig->getValue(
            self::XML_ENABLE_SANDBOX_MODE_PATH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return mixed
     */
    public function getOtpTimeout()
    {
        return $this->_scopeConfig->getValue(self::XML_OTP_TIMEOUT_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return mixed
     */
    public function getOtpResendTimeout()
    {
        return $this->_scopeConfig->getValue(self::XML_OTP_RESEND_TIMEOUT_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return mixed
     */
    public function getOTPLength()
    {
        return $this->_scopeConfig->getValue(self::XML_OTP_LENGTH_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return mixed
     */
    public function getOTPMessageConfig()
    {
        return $this->_scopeConfig->getValue(self::XML_OTP_MESSAGE_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return mixed
     */
    public function getCountryNumberCodeConfig()
    {
        return $this->_scopeConfig->getValue(self::XML_OTP_COUNTRY_NUMBER_CODE_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return string
     */
    public function generateOTP()
    {
        if ($this->getIsSandboxMode()) {
            return self::OTP_CODE_TEST;
        }
        $length = $this->getOTPLength();
        $generator = "1234567890";
        $result = "";
        for ($i = 1; $i <= $length; $i++) {
            $result .= substr($generator, (rand() % (strlen($generator))), 1);
        }
        return $result;
    }

    /**
     * @param null $otp
     * @return array|mixed|string|string[]
     */
    public function getOTPMessage($otp = null)
    {
        $rawMessage = $this->getOTPMessageConfig();
        return str_replace(self::OTP_RAW_TEXT, $otp, $rawMessage);
    }

    /**
     * @return \DateTime
     * @throws \Exception
     */
    public function getExpireOTP()
    {
        $date = new \DateTime();
        $date->add(new \DateInterval('PT' . $this->getOtpTimeout() . 'S'));
        return $date;
    }

    /**
     * @return \DateTime
     * @throws \Exception
     */
    public function getExpireOTPResend()
    {
        $date = new \DateTime();
        $date->add(new \DateInterval('PT' . $this->getOtpResendTimeout() . 'S'));
        return $date;
    }

    /**
     * @param $telephone
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendSmsOTP($telephone)
    {
        if (!$telephone) {
            return;
        }
        $storeId = $this->_storeManager->getStore()->getId();
        $postUrl = $this->getPostUrl($storeId);
        $otp = $this->generateOTP();
        $expireOTP = $this->getExpireOTP();
        $expireResendOTP = $this->getExpireOTPResend();
        $this->smsIntegrationSession->setData('expire_otp', $expireOTP);
        $this->smsIntegrationSession->setData('expire_otp_resend', $expireResendOTP);
        $this->smsIntegrationSession->setData('otp', $otp);
        $this->smsIntegrationSession->setData('phone-otp', $telephone);
        if ($this->getIsSandboxMode()) {
            return;
        }
        $countryNumberCode = $this->getCountryNumberCodeConfig();
        if (substr($telephone, 0, 1) == '0') {
            $telephone = $countryNumberCode . substr($telephone, 1);
        } elseif (substr($telephone, 0, 2) == substr($countryNumberCode, 1)) {
            $telephone = '+' . $telephone;
        }
        $from = $this->getSmSFrom($storeId);
        $text = $this->getOTPMessage($otp);
        $notifyUrl = $this->_context->getUrlBuilder()->getUrl('smsintegration/index/notify');
        $notifyContentType = 'application/json';
        $callbackData = $this->_context->getUrlBuilder()->getUrl('smsintegration/index/callback');
        $destination = ['to' => $telephone];
        $message = [
            "from" => $from,
            'destinations' =>[$destination],
            'text' => $text,
            'notifyUrl' => $notifyUrl,
            'notifyContentType' => $notifyContentType,
            'callbackData' => $callbackData
        ];

        $postData = ['messages' => [$message]];
        //encoding object
        $postDataJson = json_encode($postData);

        $username = $this->getUserName($storeId);
        $password = $this->getPassword($storeId);

        $ch = curl_init();
        $header = ["content-type: application/json", "accept: application/json"];
        // setting options
        curl_setopt($ch, CURLOPT_URL, $postUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataJson);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
    }

    /**
     * @return bool
     */
    public function validateExpireOTP($resend = false)
    {
        $expireOTP = $this->smsIntegrationSession->getData('expire_otp');
        if ($resend) {
            $expireOTP = $this->smsIntegrationSession->getData('expire_otp_resend');
        }
        $currentDateTime = new \DateTime();
        if (!$expireOTP) {
            return true;
        }
        if ($currentDateTime > $expireOTP) {
            return true;
        }
        return false;
    }

    public function getOtpResendRemaining()
    {
        $expireOtpResend = $this->smsIntegrationSession->getData('expire_otp_resend');
        return $expireOtpResend->getTimestamp() - time();
    }

    /**
     * @param array $data
     * @return array|bool[]
     */
    public function validateOTP(array $data = [])
    {
        if (isset($data['otp_code']) && $data['otp_code'] && isset($data['telephone']) && $data['telephone']) {
            $phoneOtpSession = $this->smsIntegrationSession->getData('phone-otp');
            $otpSession = $this->smsIntegrationSession->getData('otp');
            if ($this->validateExpireOTP()) {
                return ['status' => false, "message" => __('Expired OTP, please re-verify OTP')];
            }
            if (empty($phoneOtpSession) || empty($otpSession)) {
                return ['status' => false, "message" => __('Unauthenticated OTP')];
            }
            if ($otpSession != $data['otp_code']) {
                return ['status' => false, "message" => __('OTP Invalid')];
            }
            if ($phoneOtpSession != $data['telephone']) {
                return ['status' => false, "message" => __('The phone number used for OTP is not valid')];
            }
            return ['status' => true];
        } else {
            return ['status' => false, "message" => __('The otp code is empty')];
        }
    }
}
