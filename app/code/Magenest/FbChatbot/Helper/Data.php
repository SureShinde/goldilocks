<?php
namespace Magenest\FbChatbot\Helper;

use Magenest\FbChatbot\Logger\Logger;
use Magenest\FbChatbot\Model\Button;
use Magenest\FbChatbot\Model\Message;
use Magenest\FbChatbot\Setup\Patch\Data\InsertMessageData;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\Information as StoreInformation;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Magento\Directory\Model\CountryFactory;
use Magento\Payment\Model\MethodList;
use Magento\Directory\Model\TopDestinationCountries;
use Magento\Directory\Model\Country\Postcode\ConfigInterface as PostcodeConfig;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;

class Data extends \Magento\Framework\App\Helper\AbstractHelper{

    const XML_PATH_CONNECT_ENABLE = 'fb_chatbot/connect/enable';
    const XML_PATH_CONNECT_ACCESS_TOKEN = 'fb_chatbot/connect/access_token';
    const XML_PATH_CONNECT_VERIFY_TOKEN = 'fb_chatbot/connect/verification_token';
    const XML_PATH_HUMAN_SUPPORT_ENABLE = 'fb_chatbot/human_support/enable';
    const XML_PATH_HUMAN_SUPPORT_EMAIL = 'fb_chatbot/human_support/email';
    const XML_PATH_MESSAGE_GREETING = 'fb_chatbot/message/greeting';

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var StoreInformation
     */
    private $storeInformation;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Message
     */
    protected $currentMessage;

    /**
     * @var CountryCollectionFactory
     */
    protected $countryCollectionFactory;

    /**
     * @var CountryFactory
     */
    protected $countryFactory;

    /**
     * @var MethodList
     */
    protected $methodList;

    /**
     * @var TopDestinationCountries|mixed
     */
    protected $topDestinationCountries;

    /**
     * @var PostcodeConfig
     */
    protected $postCodesConfig;

    /**
     * @var RegionCollectionFactory
     */
    protected $regionCollectionFactory;


    /**
     * Data constructor.
     * @param Context $context
     * @param StateInterface $inlineTranslation
     * @param TransportBuilder $transportBuilder
     * @param Json $serializer
     * @param StoreInformation $storeInformation
     * @param StoreManagerInterface $storeManager
     * @param Logger $logger
     * @param CountryCollectionFactory $countryCollectionFactory
     * @param CountryFactory $countryFactory
     * @param MethodList $methodList
     * @param PostcodeConfig $postCodesConfig
     * @param RegionCollectionFactory $regionCollectionFactory
     * @param TopDestinationCountries|null $topDestinationCountries
     */
    public function __construct(
        Context $context,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        Json $serializer,
        StoreInformation $storeInformation,
        StoreManagerInterface $storeManager,
        Logger $logger,
        CountryCollectionFactory $countryCollectionFactory,
        CountryFactory $countryFactory,
        MethodList $methodList,
        PostcodeConfig $postCodesConfig,
        RegionCollectionFactory $regionCollectionFactory,
        TopDestinationCountries $topDestinationCountries = null
    ) {
        parent::__construct($context);
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->serializer = $serializer;
        $this->storeInformation = $storeInformation;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->countryCollectionFactory = $countryCollectionFactory;
        $this->countryFactory = $countryFactory;
        $this->methodList = $methodList;
        $this->postCodesConfig = $postCodesConfig;
        $this->regionCollectionFactory = $regionCollectionFactory;
        $this->topDestinationCountries = $topDestinationCountries ?:
            \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Directory\Model\TopDestinationCountries::class);
    }

    public function sendEmail($userData, $fanpageInfo, $userMailbox){
        try {
            $this->inlineTranslation->suspend();
            $transport = $this->transportBuilder
                ->setTemplateIdentifier('email_request_human_support_template')
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]
                )
                ->setTemplateVars([
                    'fanpageName'       => $fanpageInfo['name'],
                    'userName'          => $userData['name'],
                    'userId'            => $userData['id'],
                    'avatar'            => $userData['profile_pic'],
                    'userMailboxLink'   => "https://www.facebook.com/" . ltrim($userMailbox['data']['0']['link'],'/')
                ])
	            ->setFromByScope('general')
                ->addTo($this->getHumanSupportEmail())
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->getLogger()->error($e->getMessage());
        }
    }

    public function isEnableModule(){
        return $this->scopeConfig->getValue(self::XML_PATH_CONNECT_ENABLE,ScopeInterface::SCOPE_WEBSITE);
    }

    public function isHumanSupport(){
        return $this->scopeConfig->getValue(self::XML_PATH_HUMAN_SUPPORT_ENABLE,ScopeInterface::SCOPE_WEBSITE);
    }

    public function getHumanSupportEmail(){
        return $this->scopeConfig->getValue(self::XML_PATH_HUMAN_SUPPORT_EMAIL,ScopeInterface::SCOPE_WEBSITE);
    }

    public function getAccessToken(){
        return $this->scopeConfig->getValue(self::XML_PATH_CONNECT_ACCESS_TOKEN,ScopeInterface::SCOPE_WEBSITE);
    }

    public function getVerificationToken(){
        return $this->scopeConfig->getValue(self::XML_PATH_CONNECT_VERIFY_TOKEN,ScopeInterface::SCOPE_WEBSITE);
    }

    public function getGreetingMessage($storeId = null){
        if (!$storeId){
            $storeId = $this->storeManager->getStore()->getId();
        }
        return $this->scopeConfig->getValue(self::XML_PATH_MESSAGE_GREETING,ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param $message
     */
    public function setCurrentMessage($message){
        $this->currentMessage = $message;
    }

    /**
     * @return Message
     */
    public function getCurrentMessage(){
        return $this->currentMessage;
    }

    /**
     * @return Logger|mixed
     */
    public function getLogger()
    {
        return $this->logger;
    }
    /**
     * @param $buttonType
     * @return string
     */
    public function convertButtonType($buttonType): string
    {
        $result = null;
        if ($buttonType == Button::BUTTON_TYPE_URL){
            $result = Button::BUTTON_URL;
        }else if ($buttonType == Button::BUTTON_TYPE_TELEPHONE){
            $result = Button::BUTTON_PHONE_NUMBER;
        }else {
            $result = Button::BUTTON_POSTBACK;
        }
        return $result;
    }

    /**
     * @param $buttonType
     * @return string
     */
    public function checkPayloadOrUrl($buttonType): string
    {
        if ($buttonType == Button::BUTTON_TYPE_URL){
            $result = 'url';
        }else{
            $result = 'payload';
        }
        return $result;
    }

    /**
     * @param $string
     * @return array|bool|float|int|mixed|string|null
     */
    public function unserialize($string)
    {
        try {
            return $this->serializer->unserialize($string);
        }catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
            return $string;
        }
    }

    /**
     * @param $string
     * @return bool
     */
    public function isJson($string)
    {
        if (!empty($string)) {
            json_decode($string);

            return (json_last_error() == JSON_ERROR_NONE);
        }

        return false;
    }

    /**
     * @param $string
     * @return bool|string
     */
    public function serialize($string)
    {
        return $this->serializer->serialize($string);
    }

    /**
     * @param $email
     * @return mixed
     */
    public function checkEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * @param $value
     * @return mixed|string|string[]
     */
    public function convertSpecialValue($value){
        try {
            /** @var Store $store */
            $store = $this->storeManager->getStore();
            $storeInfo = $this->storeInformation->getStoreInformationObject($store);

            if (strpos($value,InsertMessageData::STORE_NAME) !== false){
                $storeName = trim($storeInfo->getName() ?? '') ?: 'Store';
                $value = str_replace(InsertMessageData::STORE_NAME, $storeName, $value);
            }
            if (strpos($value,InsertMessageData::STORE_ADDRESS) !== false){
                $address = $storeInfo->getData('street_line1') .' '. $storeInfo->getData('city');
                $value = str_replace(InsertMessageData::STORE_ADDRESS, $address, $value);
            }
            if (strpos($value,InsertMessageData::STORE_TELEPHONE) !== false){
                $phone = trim($storeInfo->getData('phone')) ?: '+84111222333';
                $value = str_replace(InsertMessageData::STORE_TELEPHONE, $phone, $value);
            }
            if (strpos($value,InsertMessageData::BASE_URL) !== false){
                $value = str_replace(InsertMessageData::BASE_URL, $this->storeManager->getStore()->getBaseUrl(), $value);
            }

        }catch (NoSuchEntityException $e){
            $this->_logger->error("Convert Value Errors: ". $e->getMessage());
        }

        return $value;
    }

    /**
     * @param $array
     * @param null $number
     * @return array
     */
    public function randomArray($array, $number = null): array
    {

        if ($number === null ) {
            return [$array[array_rand($array)]];
        }
        if ((int) $number === 0 || count($array) === 0) {
            return [];
        }
        if(count($array) < $number){
            $number = count($array);
        }
        $keys = (array) array_rand($array, $number);
        $results = [];
        foreach ($keys as $key) {
            $results[] = $array[$key];
        }

        return $results;
    }

    /**
     * @return StoreManagerInterface
     */
    public function getStoreManage() {
        return $this->storeManager;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getCountryOptions(): array
    {
        $countryOptions = $this->countryCollectionFactory->create()->loadByStore(
            $this->storeManager->getStore()->getId()
        )->toOptionArray(false);
        return $this->orderCountryOptions($countryOptions);
    }

    /**
     * Sort country options by top country codes.
     * @param array $countryOptions
     * @return array
     */
    protected function orderCountryOptions(array $countryOptions): array
    {
        $topCountryCodes = $this->topDestinationCountries->getTopDestinations();
        if (empty($topCountryCodes)) {
            return $countryOptions;
        }
        $headOptions = [];
        $tailOptions = [];
        foreach ($countryOptions as $countryOption) {
            if (empty($countryOption['value']) || in_array($countryOption['value'], $topCountryCodes)) {
                $headOptions[] = $countryOption;
            } else {
                $tailOptions[] = $countryOption;
            }
        }
        return array_merge($headOptions, $tailOptions);
    }

    /**
     * Get the list of regions present in the given Country
     * Returns empty array if no regions available for Country
     * @param $countryCode
     * @return mixed
     */
    public function getRegionsOfCountry($countryCode)
    {
        $regionCollection = $this->countryFactory->create()->loadByCode($countryCode)->getRegions();
        return $regionCollection->loadData()->toOptionArray(false);
    }

    /**
     * Get active/enabled payment methods
     * @param $quote
     * @return \Magento\Payment\Model\MethodInterface[]
     */
    public function getActivePaymentMethods($quote)
    {
        return $this->methodList->getAvailableMethods($quote);
    }

    /**
     * validate postcode for country
     * @param $value
     * @param $countryCode
     * @return bool|int
     */
    function validPostalCode($value, $countryCode) {
        $postCodes = $this->postCodesConfig->getPostCodes();
        if ( isset($postCodes[$countryCode]) ) {
            foreach ($postCodes[$countryCode] as $pattern){
                if(preg_match('/'.$pattern['pattern'].'/', $value))
                    return true;
            }
            return false;
        }
        return true;
    }

    /**
     * @param $countryCode
     * @return string
     */
    public function validatedPostCodeExample($countryCode) {
        $postCodes = $this->postCodesConfig->getPostCodes();
        $example = '';
        if ( isset($postCodes[$countryCode]) ) {
            foreach ($postCodes[$countryCode] as $pattern) {
                if(isset($pattern['example']))
                    $example .= $pattern['example'] . '; ';
            }
        }
        return rtrim($example, '; ');
    }

    /**
     * @param $value
     * @return bool|mixed return country
     * @throws NoSuchEntityException
     */
    public function getCountryByCode($value)
    {
        $countryOptions = $this->countryCollectionFactory->create()->loadByStore(
            $this->storeManager->getStore()->getId()
        )->toOptionArray(false);

        foreach ($countryOptions as $countryOption) {
            if($countryOption['value'] == strtoupper($value))
                return $countryOption;
        }
        return false;
    }

    /**
     * @param string $countryCode
     * @param string $regionCode
     * @return array|bool
     */
    public function getRegionByCode(string $countryCode, string $regionCode)
    {
        $region = $this->regionCollectionFactory->create()->addCountryFilter($countryCode)->addRegionCodeFilter($regionCode)->getFirstItem();
        if($region->getId()){
            return [
                'value' => $region->getId(),
                'title' => $region->getName(),
                'country_id' => $region->getCountryId(),
                'label' => $region->getName()
            ];
        }

        return false;
    }

    /**
     * check emojis from message
     * @param $string
     * @return bool
     */
    public function isStringHasEmojis($string): bool
    {
        if(is_array($string))
            return false;
        $unicodeRegexp = '([*#0-9](?>\\xEF\\xB8\\x8F)?\\xE2\\x83\\xA3|\\xC2[\\xA9\\xAE]|\\xE2..(\\xF0\\x9F\\x8F[\\xBB-\\xBF])?(?>\\xEF\\xB8\\x8F)?|\\xE3(?>\\x80[\\xB0\\xBD]|\\x8A[\\x97\\x99])(?>\\xEF\\xB8\\x8F)?|\\xF0\\x9F(?>[\\x80-\\x86].(?>\\xEF\\xB8\\x8F)?|\\x87.\\xF0\\x9F\\x87.|..(\\xF0\\x9F\\x8F[\\xBB-\\xBF])?|(((?<zwj>\\xE2\\x80\\x8D)\\xE2\\x9D\\xA4\\xEF\\xB8\\x8F\k<zwj>\\xF0\\x9F..(\k<zwj>\\xF0\\x9F\\x91.)?|(\\xE2\\x80\\x8D\\xF0\\x9F\\x91.){2,3}))?))';
        preg_match($unicodeRegexp, $string, $matches);
        return !empty($matches);
    }
}
