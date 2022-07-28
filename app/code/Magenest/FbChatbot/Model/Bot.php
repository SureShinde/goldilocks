<?php

namespace Magenest\FbChatbot\Model;

use Magenest\FbChatbot\Api\ButtonRepositoryInterface;
use Magenest\FbChatbot\Api\MessageRepositoryInterface;
use Magenest\FbChatbot\Helper\Data;
use Magenest\FbChatbot\Helper\SessionHelper;
use Magenest\FbChatbot\Model\HumanSupport\Consumer;
use Magenest\FbChatbot\Setup\Patch\Data\InsertNewMessageData;
use Magenest\FbChatbot\Ui\DataProvider\Message\Form\Modifier\CustomOptions;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Checkout\Model\ShippingInformation;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Quote\Api\ShipmentEstimationInterface;
use Magento\Quote\Model\CouponManagement;
use Magento\Quote\Model\Quote;
use Magento\QuoteGraphQl\Model\Cart\QuoteAddressFactory;
use Magento\Sales\Model\OrderRepository;
use Magento\Store\Model\StoreManagerInterface;
use Magenest\FbChatbot\Model\ResourceModel\Menu\CollectionFactory as MenuColFactory;
use Magento\Quote\Model\Quote\AddressFactory;

/**
 * Class Bot
 *
 * @package Magenest\FbChatbot\Model
 */
class Bot
{
    const ID_PAYLOAD = 'id';
    const MESSAGE_TYPE_PAYLOAD = 'messageType';
    const PRODUCTION_TYPE_PAYLOAD = 'productType';
    const CONFIRM_ORDER = 'confirmOrder';
    const CONFIRM_CREATE_NEW_ADDRESS = "confirmCreateNewAddress";
    const LENGTH_OPTIONS_SELECT = 5;
    const INVALID_MESSAGE_TYPE = 'not support other type';

	/**
	 * @var Data
	 */
	protected $helper;

	/**
	 * @var MessageBuilder
	 */
	protected $messageBuilder;

	/**
	 * @var ProductRepository
	 */
	protected $productRepository;

	/**
	 * @var CollectionFactory
	 */
	protected $categoryColFactory;

	/**
	 * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
	 */
	protected $productCollectionFactory;

	/**
	 * @var StoreManagerInterface
	 */
	protected $_storeManager;

	/**
	 * @var CategoryFactory
	 */
	protected $categoryFactory;

	/**
	 * @var \Magento\Framework\Pricing\Helper\Data
	 */
	protected $priceHelper;

	/**
	 * @var SessionHelper
	 */
	protected $sessionHelper;

	/**
	 * @var Quote
	 */
	protected $quote;

	/**
	 * @var string[]
	 */
	private $facebookScopes = [
        'name','first_name','last_name','profile_pic'
	];

	/**
	 * @var Curl
	 */
	protected $curl;

    /**
     * @var MessageRepositoryInterface
     */
    protected $messageRepository;

    /**
     * @var ButtonRepositoryInterface
     */
    protected $buttonRepository;

    protected $accessToken;

    /**
     * @var Attribute
     */
    protected $eavAttribute;

    /**
     * @var UrlBuilder
     */
    private $urlBuilder;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var PublisherInterface
     */
    private $publisher;

    /**
     * @var MenuColFactory
     */
    protected $menuColFactory;

    /**
     * @var ShipmentEstimationInterface
     */
    private $shipmentEstimationManagement;

    /**
     * @var QuoteAddressFactory
     */
    protected $quoteAddressFactory;

    /**
     * ShippingAddress field
     * @var string[]
     */
    protected $_addressNeedFill = [
        'email', 'telephone', 'firstName', 'lastName', 'street', 'country', 'region', 'city', 'postCode', 'note'
    ];

    /**
     * @var CouponManagement
     */
    protected $couponManagement;

    /**
     * @var ShippingInformation
     */
    protected $shippingInformation;

    /**
     * @var ShippingInformationManagementInterface
     */
    protected $shippingInformationManagement;

    /**
     * Bot constructor.
     * @param Data $helper
     * @param Curl $curl
     * @param MessageBuilder $messageBuilder
     * @param ProductRepository $productRepository
     * @param CollectionFactory $categoryCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param CategoryFactory $categoryFactory
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param SessionHelper $sessionHelper
     * @param MessageRepositoryInterface $messageRepository
     * @param ButtonRepositoryInterface $buttonRepository
     * @param Attribute $eavAttribute
     * @param UrlBuilder $urlBuilder
     * @param PublisherInterface $publisher
     * @param MenuColFactory $menuColFactory
     * @param AddressFactory $quoteAddressFactory
     * @param CouponManagement $couponManagement
     * @param ShippingInformation $shippingInformation
     * @param ShippingInformationManagementInterface $shippingInformationManagement
     */
	public function __construct(
		Data $helper,
		Curl $curl,
		MessageBuilder $messageBuilder,
		ProductRepository $productRepository,
		CollectionFactory $categoryCollectionFactory,
		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
		StoreManagerInterface $storeManager,
		CategoryFactory $categoryFactory,
		\Magento\Framework\Pricing\Helper\Data $priceHelper,
		SessionHelper $sessionHelper,
        MessageRepositoryInterface $messageRepository,
        ButtonRepositoryInterface $buttonRepository,
        Attribute $eavAttribute,
        UrlBuilder $urlBuilder,
        PublisherInterface $publisher,
        MenuColFactory $menuColFactory,
        AddressFactory $quoteAddressFactory,
        CouponManagement $couponManagement,
        ShippingInformation $shippingInformation,
        ShippingInformationManagementInterface $shippingInformationManagement
	) {
		$this->helper                   = $helper;
		$this->curl                     = $curl;
		$this->messageBuilder           = $messageBuilder;
		$this->productRepository        = $productRepository;
		$this->categoryColFactory       = $categoryCollectionFactory;
		$this->productCollectionFactory = $productCollectionFactory;
		$this->_storeManager            = $storeManager;
		$this->categoryFactory          = $categoryFactory;
		$this->priceHelper              = $priceHelper;
		$this->sessionHelper            = $sessionHelper;
		$this->messageRepository        = $messageRepository;
		$this->buttonRepository         = $buttonRepository;
		$this->accessToken              = $this->helper->getAccessToken();
		$this->eavAttribute             = $eavAttribute;
        $this->urlBuilder               = $urlBuilder;
        $this->publisher                = $publisher;
        $this->menuColFactory           = $menuColFactory;
        $this->quoteAddressFactory      = $quoteAddressFactory;
        $this->couponManagement         = $couponManagement;
        $this->shippingInformation = $shippingInformation;
        $this->shippingInformationManagement = $shippingInformationManagement;
	}

    /**
     * @param $messagingObject
     * @return mixed|string|null
     */
	public function getMessageFromUser($messagingObject){
	    $message = null;
        if (isset($messagingObject['message'])) {
            if ($this->getQuote()->getUsingBot() || isset($messagingObject['message']['quick_reply'])) {
                $message = $this->getPayload($messagingObject['message']);
            }
        } elseif (isset($messagingObject['postback']['payload'])) {
            $message = $messagingObject['postback']['payload'];
        }
        return $message;
    }

    /**
     * @param $recipientId
     * @param $messageText
     */
	public function sendTextMessage($recipientId, $messageText)
	{
		$this->sendMessage($recipientId, array(
			"text" => $messageText
		));
	}

    /**
     * @param $recipientId
     * @param $attachment_id
     */
	public function sendImageMessage($recipientId, $attachment_id)
    {
        $message = $this->messageBuilder->createFileMessage('image', $attachment_id);
        $this->sendMessage($recipientId, $message);
    }

    /**
     * @param $userId
     * @return array|bool|float|int|mixed|string|null
     */
	public function getUserData($userId)
	{
		$scopesField = implode(',', $this->facebookScopes);
		$url = "https://graph.facebook.com/$userId/?fields=$scopesField&access_token=$this->accessToken";
		$this->curl->get($url);
        $this->helper->getLogger()->critical('UserData ' . print_r($this->helper->unserialize($this->curl->getBody()), true));
        return $this->helper->unserialize($this->curl->getBody());
	}

    /**
     * @return array|bool|float|int|mixed|string|null
     */
	public function getFanpageInformation(){
        $url = "https://graph.facebook.com/me?fields=name&access_token=$this->accessToken";
        $this->curl->get($url);
        $this->helper->getLogger()->critical('Fanpage Information '.print_r($this->helper->unserialize($this->curl->getBody()), true));
        return $this->helper->unserialize($this->curl->getBody());
    }

    /**
     * @param $senderId
     * @return array|bool|float|int|mixed|string|null
     */
    public function getUserMailbox($senderId){
        $url = "https://graph.facebook.com/me/conversations?user_id=$senderId&access_token=$this->accessToken";
        $this->curl->get($url);
        $this->helper->getLogger()->critical('User Mailbox '.print_r($this->helper->unserialize($this->curl->getBody()), true));
        return $this->helper->unserialize($this->curl->getBody());
    }

	/**
	 * @param $text
	 */
	public function setupGreetingMessage($text)
	{
		$url = "https://graph.facebook.com/v6.0/me/messenger_profile?access_token=$this->accessToken";
		$this->sendPost($url, array(
			"greeting" => [
				array(
					"locale" => "default",
					"text"   => $text
				)
			]
		));
	}

    /**
     * @param array $actions
     * @param false $disableComposer
     */
    public function setupPersistentMenu($actions = [], $disableComposer = false)
    {
        if(empty($actions)) {
            $menus = $this->menuColFactory->create()->addFieldToFilter(Menu::ACTIVE, 1);
            foreach ($menus as $item) {
                $message = $this->messageRepository->getById($item->getData(\Magenest\FbChatbot\Model\Message::ID));
                $actions [] = ['type' => 'postback', 'title' => $item->getName(), 'payload' => $message->getName()];
            }
        }

        $this->sendPersistentMenu($actions, $disableComposer);
    }

	/**
	 * @param $actions
	 * @param bool $disableComposer
	 */
	public function sendPersistentMenu($actions, $disableComposer = false)
	{
		if (!is_array($actions)) $actions = [$actions];
		$url = "https://graph.facebook.com/v6.0/me/messenger_profile?access_token=" . $this->accessToken;
		$this->sendPost($url, array(
			"persistent_menu" => [
				array(
					"locale"                  => "default",
					"composer_input_disabled" => $disableComposer,
					"call_to_actions"         => $actions
				)
			]
		));
	}

	/**
	 * @param $postbackMessage
	 */
	public function setupGettingStarted($postbackMessage)
	{
		$url = "https://graph.facebook.com/v6.0/me/messenger_profile?access_token=" . $this->accessToken;
		$this->sendPost($url, array(
			"get_started" => array( // recipient information
				"payload" => $postbackMessage
			)
		));
	}

	/**
	 * @param $recipientId
	 */
	public function setupTyping($recipientId)
	{
		$senderAction = array(
			"recipient"     => array( // recipient information
				"id" => $recipientId
			),
			"sender_action" => "typing_on"
		);
		$url          = "https://graph.facebook.com/v6.0/me/messages?access_token=" . $this->accessToken;
		$this->sendPost($url, $senderAction);
	}

    /**
     * @param $recipientId
     * @param $message
     */
	public function sendMessage($recipientId, $message)
	{
	    if ($currentMessage = $this->helper->getCurrentMessage()){
            /**@var Message $currentMessage */
	        if ($currentMessage->getTyping() == '1'){
                sleep((int)$currentMessage->getTypingTime());
            }
        }

		$url = "https://graph.facebook.com/v6.0/me/messages?access_token=" . $this->accessToken;
		$this->sendPost($url, array(
			"recipient" => array( // recipient information
				"id" => $recipientId
			),
			"message"   => $message
		));
	}

	/**
	 * @param $url
	 * @param $data
	 */
	private function sendPost($url, $data)
	{
		$this->curl->post($url, $data);
		$this->helper->getLogger()->critical('Bot Message ' . print_r($data, true));
        $this->helper->getLogger()->critical('Result ' . print_r(json_decode($this->curl->getBody(),JSON_UNESCAPED_UNICODE), true));
	}

    /**
     * @param $receivedMessage
     * @return string
     */
	private function getPayload($receivedMessage): string
    {
        if(isset($receivedMessage['quick_reply']['payload'])) {
            return $receivedMessage['quick_reply']['payload'];
        }elseif (isset($receivedMessage['text'])){
            return $receivedMessage['text'];
        }
        return self::INVALID_MESSAGE_TYPE;
    }

    /**
     * @param $url
     * @return array|bool|float|int|mixed|string|null
     */
    public function uploadAttachment($url) {
        $requestUrl = "https://graph.facebook.com/v9.0/me/message_attachments?access_token=" . $this->accessToken;
        $data = array(
                "message"   => array(
                    "attachment" => array(
                        "type" => "image",
                        "payload" => array(
                            "is_reusable" => true,
                            "url" => $url
                        )
                    )
                )
            );
        $this->sendPost($requestUrl, $data);
        return $this->helper->unserialize($this->curl->getBody());
    }

    /**
     * @param $senderId
     * @param $messagingObject
     * @param null $isQuickReply
     * @throws NoSuchEntityException
     */
	public function handleMessage($senderId, $messagingObject, $isQuickReply = null)
	{
	    $this->setupTyping($senderId);
		if ($this->helper->isJson($messagingObject) && !is_numeric($messagingObject)) {
		    $this->handleActionPayload($senderId,$messagingObject);
		} else {
			$this->handleNormalPayload($senderId,$messagingObject, $isQuickReply);
		}
	}

    /**
     * @param $senderId
     * @param $messagingObject
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
	private function handleActionPayload($senderId,$messagingObject){
        $messagingObject = $this->helper->unserialize($messagingObject);
        if (isset($messagingObject[self::ID_PAYLOAD])) {
            if (isset($messagingObject[self::MESSAGE_TYPE_PAYLOAD]) && $messagingObject[self::MESSAGE_TYPE_PAYLOAD] == Message::MESSAGE_TYPE_CATEGORIES) {
                //Show products form category list
                $category         = $this->categoryFactory->create()->load($messagingObject[self::ID_PAYLOAD]);
                $categoryProducts = $this->getCategoryProducts($category->getProductCollection()->addAttributeToSelect('*'));
                if (!empty($categoryProducts)) {
                    $this->sendTextMessage($senderId,__('%1 Products', $category->getName())->__toString());
                    $this->showProducts($senderId, $categoryProducts,[]);
                } else {
                    $this->sendTextMessage($senderId,__("No product in %1 Category", $category->getName())->__toString());
                }
            } elseif (isset($messagingObject[self::MESSAGE_TYPE_PAYLOAD]) && $messagingObject[self::MESSAGE_TYPE_PAYLOAD] == Message::MESSAGE_TYPE_PRODUCT) {
                $this->addProductToCart($senderId,$messagingObject);
            }
        }else{
            $this->handlePayloadCreateOrder($senderId, $messagingObject);
        }
    }

    /**
     * @param $senderId
     * @param $messagingObject
     * @param null $isQuickReply
     */
    private function handleNormalPayload($senderId,$messagingObject,$isQuickReply = null){
        $email = null;
        $storedMessage = $this->getQuote()->getStoredMessage();
        if($this->helper->checkEmail($messagingObject) && $isQuickReply && $this->getQuote()->getStoredMessage()){
            $email = $messagingObject;
            $messagingObject = $this->getQuote()->getStoredMessage();
        }

        $message = $this->messageRepository->get($messagingObject);

        $this->helper->setCurrentMessage($message);

        $this->handleHumanSupport($senderId,$messagingObject);
        if(!empty($storedMessage))
            $storedMessage = $this->getStoredMessage();
        if(is_array($storedMessage)) {
            if($this->checkRequestActionMessage($senderId, $message))
                return;
            if(isset($storedMessage['coupon_fill']) && $storedMessage['coupon_fill']) {
                $this->setCoupon($senderId, $messagingObject, $storedMessage);
            }elseif (isset($storedMessage['create_order'])){
                $this->shippingAddressManagement($senderId, $storedMessage, $messagingObject);
            }
        }elseif (isset($message->getData(CustomOptions::MESSAGE_TYPES)[CustomOptions::GRID_OPTIONS_NAME])){
            $messageContents = $message->getData(CustomOptions::MESSAGE_TYPES)[CustomOptions::GRID_OPTIONS_NAME];
            foreach ($messageContents as $messageContent) {
                // skip message content not wishlist when have email
                if(!empty($email) && $messageContent[CustomOptions::FIELD_MESSAGE_TYPE_NAME] != Message::MESSAGE_TYPE_WISHLIST && $messageContent[CustomOptions::FIELD_MESSAGE_TYPE_NAME] != Message::MESSAGE_TYPE_CREATE_ORDER)
                    continue;
                $this->validateNormalMessage($senderId, $messageContent, $email);
            }
        }

    }

    /**
     * @param $senderId
     * @param $messagingObject
     */
	private function handleHumanSupport($senderId,$messagingObject){
        if ($this->helper->isHumanSupport() && $messagingObject == Message::REQUEST_HUMAN_SUPPORT) {
            $this->publisher->publish(Consumer::TOPIC_NAME, $senderId);
            $this->getQuote()->setUsingBot(0);
            $this->sessionHelper->saveQuote($this->getQuote());
        } elseif ($messagingObject == Message::CONTINUE_WITH_BOT) {
            $this->getQuote()->setUsingBot(1);
            $this->sessionHelper->saveQuote($this->getQuote());
        }
    }

    /**
     * @param $senderId
     * @param $productOptions
     * @throws NoSuchEntityException
     */
	private function addProductToCart($senderId,$productOptions){
	    $productName = null;
	    if (isset($productOptions[self::PRODUCTION_TYPE_PAYLOAD])){
	        switch ($productOptions[self::PRODUCTION_TYPE_PAYLOAD]){
                case Type::TYPE_SIMPLE:
                    $productName = $this->sessionHelper->addProductToCart($productOptions, $this->getQuote());
                    break;
                case Configurable::TYPE_CODE:
                    $count = 0;
                    $options = $this->getConfigurableProductOptions($productOptions[self::ID_PAYLOAD]);
                    foreach ($options as $key => $value){
                        $optionName = $key;
                        $key = $this->eavAttribute->getIdByCode('catalog_product', $key);
                        if (!isset($productOptions[$key])){
                            $buttons = $this->setupConfigurableProductOptionsButtons($key,$value,$productOptions);
                            $messages = $this->messageBuilder->createQuickReplyTemplate(__("Pick a %1", $optionName)->__toString(),$buttons);
                            $this->sendMessage($senderId,$messages);
                            break;
                        }
                        $count++;
                    }
                    if ($count == count($options)){
                        $productName = $this->sessionHelper->addProductToCart($productOptions, $this->getQuote());
                    }
                    break;
                default:
                    $this->sendTextMessage($senderId,__("Cannot added %1 product", $productOptions[self::PRODUCTION_TYPE_PAYLOAD])->__toString());
                    break;
            }
        }
        if (!empty($productName)){
            $this->sendTextMessage($senderId, __("%1 has been added to cart", $productName)->__toString());

            $message = $this->messageRepository->getByCode(InsertNewMessageData::WHAT_WOULD_YOU_LIKE_TO_DO_CODE);

            if(isset($message->getData(CustomOptions::MESSAGE_TYPES)[CustomOptions::GRID_OPTIONS_NAME])){
                $messageContents = $message->getData(CustomOptions::MESSAGE_TYPES)[CustomOptions::GRID_OPTIONS_NAME];
                foreach ($messageContents as $messageContent) {
                    $this->validateNormalMessage($senderId, $messageContent);
                }
            }
        }
    }

    /**
     * @param $key
     * @param $value
     * @param $data
     * @return array
     */
    private function setupConfigurableProductOptionsButtons($key,$value,$data): array
    {
        $result = [];
        if (isset($value)) {
            foreach($value as $rowKey => $rowValue){
                $data[$key] = $rowKey;
                $result [] = ['content_type' => 'text', 'title' => $rowValue, 'payload' => $this->helper->serialize($data), 'image_url' => null];
            }
        }
        return $result;
    }

    /**
     * @param $id
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getConfigurableProductOptions($id): array
    {
	    $product = $this->productRepository->getById($id);
        $data = $product->getTypeInstance()->getConfigurableOptions($product);
        $data = array_values($data);
        $options = [];
        $result = [];
        for($i = 0; $i < count($data); $i++){
            for($j = 0; $j < count($data[$i]); $j++){
                if (empty($options[$data[$i][$j]['value_index']])){
                    $options[$data[$i][$j]['value_index']] = $data[$i][$j]['option_title'];
                }
            }
            $result [$data[$i][0]['attribute_code']] = $options;
            $options = [];
        }
        return $result;
    }

    /**
     * @return array
     */
	private function setupButtonsForProducts(): array
    {
	    $message = $this->messageRepository->getByCode(InsertNewMessageData::SHOW_PRODUCT_BUTTONS_CODE);
        return !empty($message->getData(Message::MESSAGE_TYPES)[CustomOptions::GRID_OPTIONS_NAME][0][CustomOptions::GRID_TYPE_BUTTON_NAME]) ?
            $message->getData(Message::MESSAGE_TYPES)[CustomOptions::GRID_OPTIONS_NAME][0][CustomOptions::GRID_TYPE_BUTTON_NAME] :
            [];
    }

    /**
     * @param $senderId
     * @param $categoryProducts
     * @param $buttonsProduct
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
	private function showProducts($senderId, $categoryProducts,$buttonsProduct)
	{
		$elements = [];
		if ($categoryProducts) {
			foreach ($categoryProducts as $product) {
				$buttons       = $this->setupNormalButtons($buttonsProduct,$product);
				$title         = $product->getName();
				$subtitle      = $product->getData('meta_description')
					?
					$product->getData('meta_description') . "\n" .
                    $this->getFinalPrice($product)
					:
					$this->getFinalPrice($product);
				$imageUrl = null;
				if($product->getImage()) {
                    $imageUrl = $this->urlBuilder->getUrl($product->getImage(),'product_base_image');
                }
				$elements []   = $this->messageBuilder->createTemplateElement($title, $subtitle, $buttons, $imageUrl);
			}
		}
		$message = $this->messageBuilder->createGenericTemplate($elements);
		$this->sendMessage($senderId, $message);
	}

    /**
     * @param $product
     * @return float|string
     */
	public function getFinalPrice($product)
    {
        $finalPrice = $this->priceHelper->currency($product->getFinalPrice(), number_format($product->getFinalPrice(), 2), false);
        if(in_array($product->getTypeId(), ['simple', 'downloadable', 'virtual'])) {
            if($product->getFinalPrice() < $product->getPrice())
                $finalPrice .= __(" Regular Price ") . $this->priceHelper->currency($product->getPrice(), number_format($product->getPrice(), 2), false);
        }
        return $finalPrice;
    }
    /**
     * @param $productCollection
     * @return mixed
     */
    private function getCategoryProducts($productCollection){
        $productCollection->addAttributeToSelect('*')
            ->addFieldToFilter(ProductInterface::STATUS,Status::STATUS_ENABLED)
            ->addFieldToFilter(ProductInterface::VISIBILITY,array('neq' => Visibility::VISIBILITY_NOT_VISIBLE))
            ->joinField('stock_item', 'cataloginventory_stock_item', 'is_in_stock', 'product_id=entity_id', 'is_in_stock=1')
            ->addAttributeToSort('position')
            ->setPageSize(5);

        return $productCollection->getItems();
    }

    /**
     * @param $productCollection
     * @param null $productName
     * @return array
     */
	private function getRandomProducts($productCollection, $productName): array
    {
        $productCollection->addAttributeToSelect('*')
            ->addFieldToFilter(ProductInterface::NAME, array('like' => '%'.trim($productName).'%'))
            ->addFieldToFilter(ProductInterface::STATUS,Status::STATUS_ENABLED)
            ->addFieldToFilter(ProductInterface::VISIBILITY,array('neq' => Visibility::VISIBILITY_NOT_VISIBLE))
            ->joinField('stock_item', 'cataloginventory_stock_item', 'is_in_stock', 'product_id=entity_id', 'is_in_stock=1');

        return $this->helper->randomArray($productCollection->getItems(), 5);
    }

    /**
     * @param $senderId
     * @param $message
     * @param null $email
     */
	private function validateNormalMessage($senderId, $message, $email = null)
	{
		if(isset($message[CustomOptions::FIELD_MESSAGE_TYPE_NAME])){
            try {
                switch ($message[CustomOptions::FIELD_MESSAGE_TYPE_NAME]){
                    case Message::MESSAGE_TYPE_PRODUCT:
                        $this->showProductsMessage($senderId, $message);
                        break;
                    case Message::MESSAGE_TYPE_CATEGORIES:
                        $this->showCategoryMessage($senderId, $message);
                        break;
                    case Message::MESSAGE_TYPE_TEXT:
                        $this->showTextMessage($senderId, $message);
                        break;
                    case Message::MESSAGE_TYPE_QUICK_REPLY:
                        $this->showQuickReplyMessage($senderId, $message);
                        break;
                    case Message::MESSAGE_TYPE_ORDER:
                        $this->showOrdersMessage($senderId, $message[CustomOptions::GRID_TYPE_BUTTON_NAME]);
                        break;
                    case Message::MESSAGE_TYPE_WISHLIST:
                        $this->showWishlistMessage($senderId, $message[CustomOptions::GRID_TYPE_BUTTON_NAME], $email);
                        break;
                    case Message::MESSAGE_TYPE_TEXT_IMAGE:
                        $this->showTextAndImageMessage($senderId, $message);
                        break;
                    case Message::MESSAGE_TYPE_CREATE_ORDER:
                        $this->createOrder($senderId);
                        break;
                    case Message::MESSAGE_TYPE_CANCEL_ORDER_CREATION:
                        $this->cancelProcessingCreateOrder($senderId);
                        break;
                }
            }catch (NoSuchEntityException $e){
                $this->helper->getLogger()->error("Validate Normal Message Error: ". $e->getMessage());
            }
        }
	}

    /**
     * @param $senderId
     * @param $message
     * @throws NoSuchEntityException
     */
	private function showProductsMessage($senderId, $message){
        $productCollection = $this->productCollectionFactory->create();
        $productName = $message[CustomOptions::FIELD_PRODUCT_NAME] ?? '';
        $productCollection = $this->getRandomProducts($productCollection, $productName);
        $this->showProducts($senderId, $productCollection,$message[CustomOptions::GRID_TYPE_BUTTON_NAME]);
    }

    /**
     * @param $senderId
     * @param $message
     * @throws NoSuchEntityException
     */
    private function showCategoryMessage($senderId, $message){
        $categories = $this->categoryColFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter(CategoryInterface::KEY_LEVEL, $message[Message::CATEGORY_LEVEL])
            ->addFieldToFilter(CategoryInterface::KEY_IS_ACTIVE,1)
            ->addFieldToFilter(CategoryInterface::KEY_INCLUDE_IN_MENU,1)
            ->setStore($this->_storeManager->getStore())
            ->setPageSize(6);
        $elements   = [];
        if ($categories) {
            foreach ($categories as $category) {
                $buttons  = $this->setupNormalButtons($message[CustomOptions::GRID_TYPE_BUTTON_NAME],$category,$message[CustomOptions::FIELD_MESSAGE_TYPE_NAME]);
                $imageUrl = $category->getImageUrl();
                if (!empty($imageUrl)) {
                    $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
                    if (substr($imageUrl, 0, strlen($baseUrl)) !== $baseUrl) {
                        $imageUrl = $baseUrl . $imageUrl;
                    }
                } else {
                    $imageUrl = '';
                }
                $subtitle    = $category->getData('meta_description');
                $elements [] = $this->messageBuilder->createTemplateElement($category->getName(), $subtitle, $buttons, $imageUrl);
            }
            $message = $this->messageBuilder->createGenericTemplate($elements);
            $this->sendMessage($senderId, $message);
        }
    }

    /**
     * @param $senderId
     * @param $message
     * @throws NoSuchEntityException
     */
    private function showTextMessage($senderId, $message){
        if($message[CustomOptions::FIELD_INCLUDE_BUTTONS_NAME] == "1") {
            $title = $this->helper->convertSpecialValue($message[Message::TEXT]);
            $buttons = $this->setupNormalButtons($message[CustomOptions::GRID_TYPE_BUTTON_NAME]);
            $message = $this->messageBuilder->createButtonTemplate($title, $buttons);
            $this->sendMessage($senderId, $message);
        }else{
            $title = $this->helper->convertSpecialValue($message[Message::TEXT]);
            $this->sendTextMessage($senderId, __($title)->__toString());
        }
    }

    /**
     * handle data response from quick reply create order
     * @param $senderId
     * @param $messagingObject
     * @throws NoSuchEntityException
     */
    public function handlePayloadCreateOrder($senderId, $messagingObject) {
        $storedMessage = $this->getStoredMessage();
        if (isset($messagingObject[self::CONFIRM_ORDER])) {
            // confirm order before create
            if($messagingObject[self::CONFIRM_ORDER]) {
                $this->sendTextMessage($senderId, __('Placing order...')->__toString());
                $data = array("senderId" => $senderId, 'quoteId' => $this->quote->getId());
                $this->publisher->publish('chatbot.createOrder', $this->helper->serialize($data));
            }else{
                $this->getQuote()->setStoredMessage('');
                $this->sessionHelper->saveQuote($this->getQuote());
                $this->sendTextMessage($senderId, __("We have canceled the order creation process")->__toString());
            }
        }elseif (isset($messagingObject[self::CONFIRM_CREATE_NEW_ADDRESS])){
            // confirm shipping address
            if($messagingObject[self::CONFIRM_CREATE_NEW_ADDRESS]) {
                $this->getQuote()->setStoredMessage($storedMessage['create_order']);
                if($this->getQuote()->getCouponCode()){
                    $this->removeCoupon($senderId);
                }else{
                    $this->sessionHelper->saveQuote($this->getQuote());
                }
                $this->createOrder($senderId, $storedMessage['create_order']);
            }elseif (isset($messagingObject['cancel'])){
                $this->cancelProcessingCreateOrder($senderId);
            }else{
                $this->estimationShipping($senderId, $storedMessage);
            }
        }elseif (isset($messagingObject['pick_country'])) {
            if(isset($messagingObject['load_moreCountry'])) {
                $this->countryQuickReply($senderId, $messagingObject['current_page']);
            }else{
                $country = $messagingObject;
                $storedMessage['country'] = $country;
                $this->saveStoredMessage($storedMessage);
                $this->regionQuickReply($senderId, $country['value']);
            }
        }elseif (isset($messagingObject['pick_region'])) {
            if(isset($messagingObject['load_moreRegion'])) {
                $this->regionQuickReply($senderId,$storedMessage['country']['value'], $messagingObject['current_page']);
            }else{
                $regionCode = $messagingObject;
                $this->shippingAddressManagement($senderId, $storedMessage, $regionCode);
            }
        }elseif (isset($messagingObject['pick_method_shipping'])) {
            if(isset($messagingObject['carrier_code']) && isset($messagingObject['method_code'])) {
                $storedMessage['carrier_code'] = $messagingObject['carrier_code'];
                $storedMessage['method_code'] = $messagingObject['method_code'];
                $this->saveStoredMessage($storedMessage);
                $this->setShippingAddressForQuote($storedMessage);
                $this->couponQuickReply($senderId, "Do you have a coupon code?");
            }
        }elseif (isset($messagingObject['confirm_have_coupon'])) {
            if($messagingObject['confirm_have_coupon']){
                $storedMessage['coupon_fill'] = true;
                $this->saveStoredMessage($storedMessage);
                $this->sendTextMessage($senderId, "Enter your coupon code");
            }else{
                $storedMessage['coupon_fill'] = false;
                $this->saveStoredMessage($storedMessage);
                $this->paymentMethodQuickReply($senderId);
            }
        }elseif (isset($messagingObject['pick_payment_method'])) {
            $storedMessage['payment_code'] = $messagingObject['payment_code'];
            $storedMessage['payment_title'] = $messagingObject['payment_title'];
            $this->saveStoredMessage($storedMessage);
            // confirm order after filled address shipping
            $this->sendOrderConfirmation($senderId);
        }elseif (isset($messagingObject['other_payment_method'])) {
            $this->messageOtherPaymentMethod($senderId);
        }elseif (isset($messagingObject['confirm_coupon'])){
            if(!$messagingObject['confirm_coupon']){
                $this->removeCoupon($senderId, $storedMessage);
            }
            $this->paymentMethodQuickReply($senderId);
        }elseif (isset($messagingObject['confirm_postcode'])){
            // warning check postalCode is right
            if(!$messagingObject['confirm_postcode']) {
                $storedMessage['postCode'] = $messagingObject['post_code'];
                $this->saveStoredMessage($storedMessage);
                $this->sendTextMessage($senderId, __("Please enter your note")->__toString());
            }else{
                $this->sendTextMessage($senderId, __("Please enter your postCode")->__toString());
            }
        }
    }

    /**
     * Start the checkout process
     * @param $senderId
     * @param string $prevMsg
     */
    public function createOrder($senderId, string $prevMsg = '') {
        try {
            if(count($this->getQuote()->getItems()) == 0) {
                $this->sendTextMessage($senderId,__('Your shopping cart is currently empty')->__toString());
                return;
            }
            if(!$this->checkQuoteItems()){
                $this->sendTextMessage($senderId,__('Your shopping cart has an invalid item type')->__toString());
                return;
            }
            $msgName = $this->helper->getCurrentMessage() ? $this->helper->getCurrentMessage()->getName() : $prevMsg;
            $storedDataMessage = array('create_order' => $msgName );
            $storedDataMessage = $this->helper->serialize($storedDataMessage);
            $this->getQuote()->setStoredMessage($storedDataMessage);
            $this->sessionHelper->saveQuote($this->getQuote());
            $payload = [self::CONFIRM_ORDER => false];
            $buttons[] = ['type' => Button::BUTTON_POSTBACK, 'title' => __('Cancel')->__toString(), 'payload' => $this->helper->serialize($payload)];
            $message = $this->messageBuilder->createButtonTemplate(__("Send us your email to create your order (we create a new account if you haven't already)")->__toString(), $buttons);
            $this->sendMessage($senderId, $message);
        }catch (NoSuchEntityException $e) {
            $this->sendMessage($senderId, __("Sorry, there has been an error processing your request. Please try again later.")->__toString());
            $this->helper->getLogger()->error("process create order failed: ". $e->getMessage());
        }
    }

    /**
     * check with product type not support
     * @return bool
     */
    public function checkQuoteItems(): bool
    {
        $items = $this->getQuote()->getItems();
        foreach ($items as $item) {
            if(!in_array($item->getProductType(), [Type::TYPE_SIMPLE, Configurable::TYPE_CODE])){
                return false;
            }
        }
        return true;
    }
    /**
     * manager shipping address for user enter
     * @param $senderId
     * @param $dataAddress
     * @param $data
     * @throws NoSuchEntityException
     */
    public function shippingAddressManagement($senderId, $dataAddress, $data)
    {
        try {
            if(!$this->checkQuoteItems()){
                $this->sendTextMessage($senderId,__('Your shopping cart has an invalid item type')->__toString());
                $this->cancelProcessingCreateOrder($senderId);
                return;
            }
            foreach ($this->_addressNeedFill as $key => $item) {
                if(empty($dataAddress[$item])) {
                    $valueValid = $this->validateShippingAddress($item, $data, $dataAddress);
                    if(!$valueValid) {
                        $this->handleInvalidAddress($senderId, $item, $data, $dataAddress);
                        break;
                    }
                    $dataAddress[$item] = $valueValid;
                    $this->getQuote()->setStoredMessage($this->helper->serialize($dataAddress));
                    $this->sessionHelper->saveQuote($this->getQuote());
                    if(count($this->_addressNeedFill) > $key + 1){
                        $fieldAddress = "Please enter your ".$this->_addressNeedFill[$key + 1];
                        if($item == "street"){// if street then next address is country
                            $this->countryQuickReply($senderId);
                        }elseif ($item == 'country'){
                            $this->regionQuickReply($senderId, $dataAddress['country']['value']);
                        }else{
                            $this->sendTextMessage($senderId, __($fieldAddress)->__toString());
                        }
                    }elseif(count($this->_addressNeedFill) == $key + 1){
                        $this->confirmNewAddress($senderId, $dataAddress);
                    }
                    break;
                }elseif (count($this->_addressNeedFill) < count($dataAddress) - 1) {
                    if(!isset($dataAddress['coupon_fill'])){
                        $this->couponQuickReply($senderId, "Do you have a coupon code?");
                    }elseif (!isset($dataAddress['payment_code'])) {
                        $this->paymentMethodQuickReply($senderId);
                    }else{
                        $this->sendOrderConfirmation($senderId);
                    }
                    break;
                }elseif(count($this->_addressNeedFill) == $key + 1) {
                    $this->confirmNewAddress($senderId, $dataAddress);
                }
            }
        }catch (\Throwable $e) {
            $this->helper->getLogger()->error("something error in processing get order data: ". $e->getMessage());
            $this->cancelProcessingCreateOrder($senderId);
        }
    }

    /**
     * @param $typeAddress
     * @param $value
     * @param $shippingAddress
     * @return false|string|string[]|null
     * @throws NoSuchEntityException
     */
    public function validateShippingAddress($typeAddress, $value, $shippingAddress)
    {
        if($value == self::INVALID_MESSAGE_TYPE || $this->helper->isStringHasEmojis($value))
            return false;

        switch ($typeAddress){
            case "email":
                 if($this->helper->checkEmail($value))
                     return $value;
                 return false;
            case "postCode":
                if(isset($shippingAddress['country']['value'])) {
                    if($this->helper->validPostalCode($value, $shippingAddress['country']['value']))
                        return $value;
                }
                return false;
            case "country":
                 return $this->helper->getCountryByCode($value);
            case 'region':
                if(isset($shippingAddress['country']['is_region_required']) && !is_array($value)){
                    return $this->helper->getRegionByCode($shippingAddress['country']['value'], $value);
                }
                return $value;
            default:
                return $value;
        }
    }

    /**
     * handle when address invalid
     * @param $senderId
     * @param $type
     * @param $value
     * @param $data
     * @throws NoSuchEntityException
     */
    public function handleInvalidAddress($senderId, $type, $value, $data) {
        switch ($type) {
            case 'country':
                $this->sendTextMessage($senderId, __("Invalid Country")->__toString());
                $this->countryQuickReply($senderId);
                break;
            case 'region':
                $this->sendTextMessage($senderId, __("Invalid State/Province")->__toString());
                $this->regionQuickReply($senderId, $data['country']['value']);
                break;
            case 'postCode':
                if($value == self::INVALID_MESSAGE_TYPE || $this->helper->isStringHasEmojis($value)){
                    $msg = "Your {$type} invalid please enter again";
                    $this->sendTextMessage($senderId, __($msg)->__toString());
                    break;
                }
                $this->noticeWrongPostalCode($senderId, $value, $data['country']['value']);
                break;
            default:
                $msg = "Your {$type} invalid please enter again";
                $this->sendTextMessage($senderId, __($msg)->__toString());
                break;
        }
    }

    /**
     * @param $senderId
     * @param $postCode
     * @param $countryCode
     */
    public function noticeWrongPostalCode($senderId, $postCode, $countryCode) {
        $btnContinue = [ 'title' => __("Re-enter")->__toString(), 'payload' =>  ['confirm_postcode' => true]];
        $btnCancel = [ 'title' => __("Ignore")->__toString(), 'payload' =>  ['confirm_postcode' => false, 'post_code' => $postCode]];
        $buttons = $this->createButtonQuickReply(array($btnContinue, $btnCancel));
        $notice = __("Provided Zip/Postal Code seems to be invalid.")->__toString();
        $notice .= __(" Example: ")->__toString(). $this->helper->validatedPostCodeExample($countryCode);
        $notice .= __(". If you believe it is the right one you can ignore this notice.")->__toString();
        $message = $this->messageBuilder->createQuickReplyTemplate($notice, $buttons);
        $this->sendMessage($senderId, $message);
    }
    /**
     * cancel process create order
     * @param $senderId
     */
    public function cancelProcessingCreateOrder($senderId)
    {
        try {
            $storedMessage = $this->helper->unserialize($this->quote->getStoredMessage());
            if(isset($storedMessage['create_order'])) {
                $this->getQuote()->setStoredMessage('');
                if ($this->getQuote()->getCouponCode()) {
                    $this->removeCoupon($senderId);
                } else {
                    $this->sessionHelper->saveQuote($this->getQuote());
                }
                $this->sendTextMessage($senderId, __("We have canceled the order creation process")->__toString());
            }else{
                $this->sendTextMessage($senderId, __("You haven't initiated the order yet")->__toString());
            }
        }catch (\Throwable $e) {
            $this->helper->getLogger()->error("Error when cancel order creation: ". $e->getMessage());
        }
    }

    /**
     * @param $senderId
     * @param $message
     * @return bool
     */
    public function checkRequestActionMessage($senderId, $message): bool
    {
        if(isset($message->getData(CustomOptions::MESSAGE_TYPES)[CustomOptions::GRID_OPTIONS_NAME])) {
            $messageContents = $message->getData(CustomOptions::MESSAGE_TYPES)[CustomOptions::GRID_OPTIONS_NAME];
            $isCancelCreateOrder = false;
            foreach ($messageContents as $messageContent) {
                    if($messageContent['message_type'] == Message::MESSAGE_TYPE_CANCEL_ORDER_CREATION) {
                        $isCancelCreateOrder = true;
                        break;
                    }
            }
            if($isCancelCreateOrder){
                foreach ($messageContents as $messageContent) {
                    $this->validateNormalMessage($senderId, $messageContent);
                }
                return true;
            }
        }
        return false;
    }
    /**
     * quick reply user have coupon code
     * @param $senderId
     * @param $title
     */
    public function couponQuickReply($senderId, $title) {
        $title = __($title)->__toString();
        $btnYes = [ 'title' => __('Yes')->__toString(), 'payload' => ["confirm_have_coupon" => true] ];
        $btnNo = [ 'title' => __('No')->__toString(), 'payload' => ["confirm_have_coupon" => false] ];
        $buttons = $this->createButtonQuickReply(array($btnYes, $btnNo));
        $message = $this->messageBuilder->createQuickReplyTemplate($title, $buttons);
        $this->sendMessage($senderId, $message);
    }

    /**
     * show message confirm use coupon
     * @param $senderId
     */
    public function confirmCouponQuickReply($senderId) {
        $btnContinue = [ 'title' => __("Continue")->__toString(), 'payload' =>  ['confirm_coupon' => true]];
        $btnCancel = [ 'title' => __("Cancel coupon")->__toString(), 'payload' =>  ['confirm_coupon' => false]];
        $buttons = $this->createButtonQuickReply(array($btnContinue, $btnCancel));
        $message = $this->messageBuilder->createQuickReplyTemplate(__("Your coupon was successfully applied.")->__toString(), $buttons);
        $this->sendMessage($senderId, $message);
    }
    /**
     * @param $senderId
     * @param $coupon
     * @param $storedMessage
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function setCoupon($senderId, $coupon, $storedMessage) {
        try{
            $storedMessage['coupon_fill'] = false;
            $this->saveStoredMessage($storedMessage);
            $this->couponManagement->set($this->quote->getId(), $coupon);
            $this->confirmCouponQuickReply($senderId);
        }catch (NoSuchEntityException $e){
            $this->couponQuickReply($senderId, $e->getMessage());
        }
    }

    /**
     * @param $senderId
     * @param array $storedMessage
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function removeCoupon($senderId, array $storedMessage = []) {
        try{
            if($storedMessage) {
                $storedMessage['coupon_fill'] = false;
                $this->getQuote()->setStoredMessage($this->helper->serialize($storedMessage));
            }
            $this->couponManagement->remove($this->quote->getId());
            $this->sendTextMessage($senderId, __('Your coupon was successfully removed.')->__toString());
        }catch (NoSuchEntityException $e){
            $this->couponQuickReply($senderId, $e->getMessage());
        }
    }
    /**
     * show quick reply pick payment method
     * @param $senderId
     */
    public function paymentMethodQuickReply($senderId) {
        $paymentMethod = $this->helper->getActivePaymentMethods($this->getQuote());
        $buttons = [];
        $allowMethod = ['checkmo', 'cashondelivery', 'free'];
        $free = false;
        foreach($paymentMethod as $item){
            if(in_array($item->getCode(), $allowMethod)) {
                $dataPayload = array(
                    'pick_payment_method' => true,
                    'payment_code' => $item->getCode(),
                    'payment_title' => $item->getTitle()
                );
                $button = ['content_type' => 'text', 'title' => $item->getTitle(), 'payload' => $this->helper->serialize($dataPayload), 'image_url' => null];
                if($item->getCode() == 'free') {
                    $free = true;
                    $buttons = [$button];
                    break;
                }
                $buttons [] = $button;
            }
        }
        if(!$free) {
            $payload = ['other_payment_method' => true];
            $buttons [] = ['content_type' => 'text', 'title' => __("Other method")->__toString(), 'payload' => $this->helper->serialize($payload), 'image_url' => null];
        }
        $message = $this->messageBuilder->createQuickReplyTemplate(__("Choose payment method")->__toString(), $buttons);
        $this->sendMessage($senderId, $message);
    }


    /**
     * show message to redirect to page checkout
     * @param $senderId
     * @throws NoSuchEntityException
     */
    public function messageOtherPaymentMethod($senderId) {
        $title = __("Redirect to my website to complete payment")->__toString();
        $url = $this->_storeManager->getStore()->getBaseUrl(). "chatbot/webhook/checkout?quote_id={$this->getQuote()->getId()}&checkout_page=true";
        $buttons[] = ['type' => Button::BUTTON_URL, 'title' => __('Redirect Now')->getText(), 'url' => $url];
        $message = $this->messageBuilder->createButtonTemplate($title, $buttons);
        $this->sendMessage($senderId, $message);
    }
    /**
     * estimation shipping cost from shipping address
     * @param $senderId
     * @param $shippingAddress
     */
    public function estimationShipping($senderId, $shippingAddress) {
        $shippingAddress = $this->getAddress($shippingAddress);
        $estimationShipping = $this->getShipmentEstimationManagement()
            ->estimateByExtendedAddress($this->getQuote()->getId(), $shippingAddress);
        $buttons = [];
        $description_method = chr(10);
        foreach($estimationShipping as $item){
            $dataPayload = array(
                'pick_method_shipping' => true,
                'carrier_code' => $item->getCarrierCode(),
                'method_code' => $item->getMethodCode()
            );
            $description_method .= $item->getCarrierTitle() . ' - ' . $item->getMethodTitle() .chr(10);
            $buttons [] = ['content_type' => 'text', 'title' => $item->getCarrierTitle(). ': '. $this->priceHelper->currency($item->getPriceInclTax(), number_format(50, 2), false), 'payload' => $this->helper->serialize($dataPayload), 'image_url' => null];
        }
        $message = $this->messageBuilder->createQuickReplyTemplate(__("Choose shipping method")->__toString() . $description_method, $buttons);
        $this->sendMessage($senderId, $message);
    }

    /**
     * @return ShipmentEstimationInterface|mixed
     */
    private function getShipmentEstimationManagement()
    {
        if ($this->shipmentEstimationManagement === null) {
            $this->shipmentEstimationManagement = ObjectManager::getInstance()
                ->get(ShipmentEstimationInterface::class);
        }
        return $this->shipmentEstimationManagement;
    }

    /**
     * @param $shippingData
     * @return mixed
     */
    public function getAddress($shippingData) {
        $address = $this->quoteAddressFactory->create();
        if(is_array($shippingData)) {
            if(isset($shippingData['region']['value'])) {
                $address->setRegionId($shippingData['region']['value']);
                $address->setRegion($shippingData['region']['label']);
            }else{
                $address->setRegion($shippingData['region']);
            }
            $address->setSaveInAddressBook(0);
            $address->setStreet($shippingData['street']);
            $address->setCity($shippingData['city']);
            $address->setCountryId($shippingData['country']['value']);
            $address->setPostCode($shippingData['postCode']);
            $address->setFirstName($shippingData['firstName']);
            $address->setLastName($shippingData['lastName']);
            $address->setTelephone($shippingData['telephone']);
        }
        return $address;
    }

    /**
     * @param $shippingData
     */
    public function setShippingAddressForQuote($shippingData) {
        $address = $this->getAddress($shippingData);
        $shippingInformation = $this->shippingInformation;
        $shippingInformation->setShippingAddress($address);
        $shippingInformation->setBillingAddress($address);
        $shippingInformation->setShippingCarrierCode($shippingData['carrier_code']);
        $shippingInformation->setShippingMethodCode($shippingData['method_code']);
        $this->shippingInformationManagement->saveAddressInformation($this->getQuote()->getId(), $shippingInformation);
    }

    /**
     * confirm create new shipping address
     * @param $senderId
     * @param $dataAddress
     */
    public function confirmNewAddress($senderId, $dataAddress) {
        $btnReEnter = [
            'title' => __("re-enter the address")->__toString(),
            "payload" => [
                "confirmCreateNewAddress" => true
            ]
        ];
        $btnContinue = [
            'title' => __("Continue")->__toString(),
            "payload" => [
                "confirmCreateNewAddress" => false
            ]
        ];
        $btnCancel = [
            'title' => __("Cancel")->__toString(),
            "payload" => [
                "confirmCreateNewAddress" => false,
                'cancel' => true
            ]
        ];
        try {
            $title = __("Your shipping address: ")->__toString();
            $region = $dataAddress['region']['label'] ?? $dataAddress['region'];
            $title .= $dataAddress['email']. ', '. $dataAddress['firstName'].' '. $dataAddress['lastName']. ', ';
            $title .= $dataAddress['street']. ', '.$dataAddress['city']. ", ".$region. ", ". $dataAddress['country']['label']. ", postcode: ".$dataAddress['postCode'];
            $title .= __(", telephone: %1", $dataAddress['telephone']);
            $title .= __(", your note: %1", $dataAddress['note']);
            $buttons = $this->createButtonQuickReply(array($btnReEnter, $btnContinue, $btnCancel));
            $message = $this->messageBuilder->createQuickReplyTemplate($title, $buttons);
            $this->sendMessage($senderId, $message);
        }catch (\Throwable $e){
            $buttons = $this->createButtonQuickReply(array($btnReEnter, $btnCancel));
            $message = $this->messageBuilder->createQuickReplyTemplate(__("Your shipping address invalid!")->__toString(), $buttons);
            $this->sendMessage($senderId, $message);
        }
    }
    /**
     * @param $buttons
     * @return array
     */
    public function createButtonQuickReply($buttons) {
        $result = [];
        foreach ($buttons as $button) {
            $result[] = ['content_type' => 'text', 'title' => $button['title'], 'payload' => $this->helper->serialize($button['payload']), 'image_url' => null];
        }
        return $result;
    }

    /**
     * @param $senderId
     * @param int $currPage
     * @throws NoSuchEntityException
     */
    public function countryQuickReply($senderId, int $currPage = 0) {
        $title = __("Choose your country")->__toString();
        $allCountries = $this->helper->getCountryOptions();
        $countries = $this->getCountries($currPage);
        $buttons = $this->setUpButtonPickQuickReply($allCountries, $countries, 'pick_country', 'load_moreCountry', $currPage);
        $message = $this->messageBuilder->createQuickReplyTemplate($title, $buttons);
        $this->sendMessage($senderId, $message);
    }

    /**
     * @param int $ofSet
     * @param int $length
     * @return array
     * @throws NoSuchEntityException
     */
    public function getCountries(int $ofSet = 0 , int $length = self::LENGTH_OPTIONS_SELECT) {
        $allCountry = $this->helper->getCountryOptions();
        return array_slice($allCountry, $ofSet, $length);
    }

    /**
     * @param $allItem
     * @param $items
     * @param $typePick
     * @param $loadMoreType
     * @param int $currPage
     * @return array
     */
    public function setUpButtonPickQuickReply($allItem, $items, $typePick, $loadMoreType, int $currPage = self::LENGTH_OPTIONS_SELECT) {
        $result = [];
        if($currPage >= self::LENGTH_OPTIONS_SELECT) {
            $dataLoadPrevCountry = array(
                'current_page' => $currPage - self::LENGTH_OPTIONS_SELECT,
                $loadMoreType => false,
                $typePick => true
            );
            $result[] = ['content_type' => 'text', 'title' => __("Load previous")->__toString(), 'payload' => $this->helper->serialize($dataLoadPrevCountry), 'image_url' => null];
        }
        foreach($items as $item){
            $dataPayload = $item;
            $dataPayload[$typePick] = true;
            $result [] = ['content_type' => 'text', 'title' => $item['label'], 'payload' => $this->helper->serialize($dataPayload), 'image_url' => null];
        }
        if(count($allItem) > (count($items) + $currPage)) {
            $dataLoadMoreCountry = array(
                'current_page' => $currPage + self::LENGTH_OPTIONS_SELECT,
                $loadMoreType => true,
                $typePick => true
            );
            $result[count($result)] = ['content_type' => 'text', 'title' => __("Load more...")->__toString(), 'payload' => $this->helper->serialize($dataLoadMoreCountry), 'image_url' => null];
        }
        return $result;
    }

    /**
     * show options region
     * @param $senderId
     * @param $countryCode
     * @param int $currPage
     */
    public function regionQuickReply($senderId, $countryCode, int $currPage = 1)
    {
        $allRegion = $this->helper->getRegionsOfCountry($countryCode);
        $regions = $this->getRegion($countryCode, $currPage);
        if(count($regions) > 0) {
            $buttons = $this->setUpButtonPickQuickReply($allRegion, $regions, 'pick_region', 'load_moreRegion', $currPage);
            $message = $this->messageBuilder->createQuickReplyTemplate(__("Choose your State / Province")->__toString(), $buttons);
            $this->sendMessage($senderId, $message);
        }else{
            $this->sendTextMessage($senderId, __('Please enter your State/Province')->__toString());
        }
    }

    /**
     * @param $countryCode
     * @param int $ofSet
     * @param int $length
     * @return mixed
     */
    public function getRegion($countryCode, int $ofSet = 1, int $length = self::LENGTH_OPTIONS_SELECT) {
        $allRegion = $this->helper->getRegionsOfCountry($countryCode);
        return array_slice($allRegion, $ofSet, $length);
    }

    /**
     * @return array|bool|float|int|mixed|string|null
     */
    public function getStoredMessage() {
        return $this->helper->unserialize($this->getQuote()->getStoredMessage());
    }

    /**
     * @param $storedMessage
     */
    public function saveStoredMessage($storedMessage) {
        $this->getQuote()->setStoredMessage($this->helper->serialize($storedMessage));
        $this->sessionHelper->saveQuote($this->getQuote());
    }

    /**
     * send order preview after choose estimateShipping
     * @param $senderId
     * @throws NoSuchEntityException
     */
    public function sendOrderConfirmation($senderId){
        try {
            $payload = $this->createReceiptData();
            $message = $this->messageBuilder->createReceiptTemplate($payload);
            $this->sendMessage($senderId, $message);
            $title = __("Confirm your order")->__toString();
            $btnPlace = [
                'title'   => __("Place Order")->__toString(),
                'payload' => ["confirmOrder" => true]
            ];
            $btnCancel = [
                'title'   => __("Cancel")->__toString(),
                'payload' => ["confirmOrder" => false]
            ];
            $buttons = $this->createButtonQuickReply(array($btnPlace, $btnCancel));
            $message = $this->messageBuilder->createQuickReplyTemplate($title, $buttons);
            $this->sendMessage($senderId, $message);
        }catch (\Exception $e) {
            $this->helper->getLogger()->error("something error in processing create receipt template: ". $e->getMessage());
            $this->cancelProcessingCreateOrder($senderId);
        }
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function createReceiptData(): array
    {
        $quote = $this->getQuote();
        $storedMessage = $this->helper->unserialize($quote->getStoredMessage());
        $quote->collectTotals();
        $recipient_name = $storedMessage['firstName'].' '.$storedMessage['lastName'];
        $currency = $this->_storeManager->getStore()->getBaseCurrencyCode();
        $state = $storedMessage['region']['label'] ?? $storedMessage['region'];
        $description = (string)$quote->getShippingAddress()->getDiscountDescription() ?: '';
        $elements = [];
        foreach ($quote->getItems() as $item) {
            $imageUrl = '';
            if($item->getProduct()->getSmallImage()) {
                $imageUrl = $this->urlBuilder->getUrl($item->getProduct()->getSmallImage(), 'product_base_image');
            }
            $elements[] = array(
                "title" => $item->getName(),
                "subtitle" => $item->getDisciption()??'',
                "quantity" => $item->getQty(),
                "price" => $item->getBasePrice(),
                "currency" => $currency,
                "image_url" => $imageUrl
            );
        }
        $payload = array(
            "template_type" => "receipt",
            "recipient_name" => $recipient_name,
            "order_number" => $quote->getId(),
            "currency" => $currency,
            "payment_method" => $storedMessage['payment_title'],
            "order_url" => $this->_storeManager->getStore()->getBaseUrl(). "chatbot/webhook/checkout?quote_id={$quote->getId()}",
            "address" => array(
                "street_1" => $storedMessage['street'],
                "street_2" => '',
                "city" => $storedMessage['city'],
                "state" => $state. ', ' .$storedMessage['country']['label']. ', ',
                "postal_code" => $storedMessage['postCode'],
                "country" => $storedMessage['country']['label']
            ),
            "summary" => array(
                "subtotal" => $quote->getSubtotal(),
                "shipping_cost" => $quote->getShippingAddress()->getShippingAmount(),
                "total_tax" => $quote->getTotals()['tax']->getValue(),
                "total_cost" => $quote->getGrandTotal()
            ),
            "adjustments" => array(
                array(
                    "name" => strlen($description) ? __('Discount (%1)', $description)->__toString() : __('Discount')->__toString(),
                    "amount" =>  $quote->getShippingAddress()->getDiscountAmount()
                )
            ),
            "elements" => $elements
        );
        if($quote->getShippingAddress()->getDiscountAmount() >= 0) {
            unset($payload['adjustments']);
        }

        return $payload;
    }
    /**
     * @param $senderId
     * @param $message
     */
    private function showQuickReplyMessage($senderId, $message){
        $title = $this->helper->convertSpecialValue($message[Message::TEXT]);
        $buttons = $this->setupQuickReplyButton($message[CustomOptions::GRID_TYPE_BUTTON_NAME]);
        $message = $this->messageBuilder->createQuickReplyTemplate($title, $buttons);
        $this->sendMessage($senderId, $message);
    }

    /**
     * @param $senderId
     * @param $MessageButtons
     * @throws NoSuchEntityException
     */
    private function showOrdersMessage($senderId, $MessageButtons)
    {
        $elements = [];
        $orders = $this->sessionHelper->getOrderFromBot($senderId);
        if ($orders) {
            $this->sendTextMessage($senderId,__("Hey nice! Here I'm with your previous %1 orders", count($orders))->__toString());
            foreach ($orders as $order) {
                $orderItems = $order->getItems();
                foreach ($orderItems as $item) {
                    $product = $this->productRepository->getById($item->getProductId());
                    $buttons = $this->setupOrderButtons($order['entity_id'], $product, $MessageButtons);
                    $qty = number_format($order->getTotalQtyOrdered());
                    $countItem = __("%1 item(s)", $qty)->__toString();
                    $title = '#'.$order->getIncrementId(). ' | ' .$countItem. ' | '. $this->priceHelper->currency($order->getGrandTotal(), number_format(50, 2), false);
                    $subtitle = __("Ordered on ")->__toString() . date('d-F-Y', strtotime($order->getCreatedAt()));
                    $imageUrl = null;
                    if($product->getImage()) {
                        $imageUrl = $this->urlBuilder->getUrl($product->getImage(), 'product_base_image');
                        $elements [] = $this->messageBuilder->createTemplateElement($title, $subtitle, $buttons, $imageUrl);
                    }
                    break;
                }
            }
            $message = $this->messageBuilder->createGenericTemplate($elements);
            $this->sendMessage($senderId, $message);
        }else {
            $this->sendTextMessage($senderId,__("Opp! You have not created any order from chat bot")->__toString());
        }
    }

    /**
     * @param $senderId
     * @param $messageButtons
     * @param null $email
     * @throws NoSuchEntityException
     */
    private function showWishlistMessage($senderId, $messageButtons, $email=null) {
        if ($email){
            $wishlist = $this->sessionHelper->getWishlist($email);
            $products = [];
            if($wishlist == 'notRegistered') {
                $this->sendTextMessage($senderId,__("Email not registered account!")->__toString());
            }elseif ($wishlist){
                $this->sendTextMessage($senderId,__("I found %1 item(s) in your wishlist", count($wishlist))->__toString());
                foreach ($wishlist as $item){
                    $product = $this->productRepository->getById($item['product_id']);
                    $products[] = $product;
                }
                $this->showProducts($senderId,$products, $messageButtons);
            }else{
                $this->sendTextMessage($senderId,__("Your wishlist is empty")->__toString());
            }
        }else{
            $this->getQuote()->setStoredMessage($this->helper->getCurrentMessage()->getName());
            $this->sessionHelper->saveQuote($this->getQuote());
            $message = $this->messageBuilder->createQuickReplyTemplate(__('Send us your email to get your wishlist')->__toString(), [["content_type"=>"user_email"]]);
            $this->sendMessage($senderId,$message);
        }
    }

    /**
     * @param $senderId
     * @param $message
     * @throws NoSuchEntityException
     */
    private function showTextAndImageMessage($senderId, $message) {
        if(isset($message[CustomOptions::FIELD_IMAGE_NAME][0]['attachment_id'])) {
            $this->sendImageMessage($senderId,$message[CustomOptions::FIELD_IMAGE_NAME][0]['attachment_id']);
        }
        $text = $this->helper->convertSpecialValue($message[Message::TEXT]);
        if($message[CustomOptions::FIELD_INCLUDE_BUTTONS_NAME] == "1") {
            $buttons = $this->setupNormalButtons($message[CustomOptions::GRID_TYPE_BUTTON_NAME]);
            $message = $this->messageBuilder->createButtonTemplate($text, $buttons);
            $this->sendMessage($senderId, $message);
        }else{
            $this->sendTextMessage($senderId, __($text)->__toString());
        }
    }

    /**
     * @param $buttons
     * @param null $item
     * @param int $messageType
     * @return array
     * @throws NoSuchEntityException
     */
	private function setupNormalButtons($buttons,$item = null,$messageType = Message::MESSAGE_TYPE_PRODUCT): array
    {
		$result = [];
		if (empty($buttons)){
		    $buttons = $this->setupButtonsForProducts();
        }
		if (isset($buttons)){
            foreach ($buttons as $button){
                if ($button[Button::BUTTON_TYPE] == Button::BUTTON_TYPE_ACTION){
                    $buttonLabel = $button[CustomOptions::FIELD_BUTTON_LABEL];
                    if (isset($button[CustomOptions::FIELD_BUTTON_ACTION])){
                        $button = $this->buttonRepository->getById($button[CustomOptions::FIELD_BUTTON_ACTION])->getData();
                    }
                    if ($button[Button::CODE] == Button::MODIFY_CART_AND_CHECKOUT_CODE || $button[Button::CODE] == Button::WRITE_PRODUCT_REVIEW_CODE ||
                        (!empty($item) && ($button[Button::CODE] == Button::VIEW_PRODUCT_DETAIL_CODE || $button[Button::CODE] == Button::VIEW_CATEGORY_DETAIL_CODE))
                    ){
                        $url = null;
                        switch ($button[Button::CODE]){
                            case Button::MODIFY_CART_AND_CHECKOUT_CODE:
                                $url = $this->_storeManager->getStore()->getBaseUrl(). "chatbot/webhook/checkout?quote_id={$this->getQuote()->getId()}";
                                break;
                            case Button::VIEW_PRODUCT_DETAIL_CODE:
                                $url = $item->getProductUrl();
                                break;
                            case Button::WRITE_PRODUCT_REVIEW_CODE:
                                $url = $item->getProductUrl().'#reviews';
                                break;
                            default:
                                $url = $item->getUrl();
                                break;
                        }
                        $result [] = ['type' => Button::BUTTON_URL, 'title' => $buttonLabel, 'url' => $url];
                    }elseif (!empty($item)){
                        $data       = [
                            self::MESSAGE_TYPE_PAYLOAD => $messageType,
                            self::PRODUCTION_TYPE_PAYLOAD => $item->getTypeId(),
                            self::ID_PAYLOAD => $item->getId()
                        ];
                        $result [] = ['type' => Button::BUTTON_POSTBACK, 'title' => $buttonLabel, 'payload' => $this->helper->serialize($data)];
                    }
                }elseif ($button[Button::BUTTON_TYPE] == Button::BUTTON_TYPE_TEXT){
                    $message = $this->messageRepository->getById($button[CustomOptions::FIELD_BUTTON_ACTION]);
                    $result [] = ['type' => $this->helper->convertButtonType($button[Button::BUTTON_TYPE]), 'title' => $button[CustomOptions::FIELD_BUTTON_LABEL], $this->helper->checkPayloadOrUrl($button[Button::BUTTON_TYPE]) => $message->getName()];
                }else{
                    $result [] = ['type' => $this->helper->convertButtonType($button[Button::BUTTON_TYPE]), 'title' => $button[CustomOptions::FIELD_BUTTON_LABEL], $this->helper->checkPayloadOrUrl($button[Button::BUTTON_TYPE]) => $this->helper->convertSpecialValue($button[CustomOptions::FIELD_BUTTON_ACTION])];
                }
            }
        }
		return $result;
	}

    /**
     * @param $orderId
     * @param $item
     * @param $buttons
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setupOrderButtons($orderId, $item, $buttons): array
    {
        $result = [];

        foreach ($buttons as $button){
            if ($button[Button::BUTTON_TYPE] == Button::BUTTON_TYPE_ACTION) {
                $buttonLabel = $button[CustomOptions::FIELD_BUTTON_LABEL];
                if (isset($button[CustomOptions::FIELD_BUTTON_ACTION])) {
                    $button = $this->buttonRepository->getById($button[CustomOptions::FIELD_BUTTON_ACTION])->getData();
                }
                if ($button[Button::CODE] == Button::VIEW_ORDER_DETAIL_CODE || $button[Button::CODE] == Button::WRITE_PRODUCT_REVIEW_CODE) {
                    $url = null;
                    switch ($button[Button::CODE]) {
                        case Button::VIEW_ORDER_DETAIL_CODE:
                            $url = $this->_storeManager->getStore()->getBaseUrl() . "sales/order/view/order_id/{$orderId}";
                            break;
                        case Button::WRITE_PRODUCT_REVIEW_CODE:
                            $url = $item->getProductUrl().'#reviews';
                            break;
                        default:
                            $url = $item->getUrl();
                            break;
                    }
                    $result [] = ['type' => Button::BUTTON_URL, 'title' => $buttonLabel, 'url' => $url];
                }
            }elseif ($button[Button::BUTTON_TYPE] == Button::BUTTON_TYPE_TEXT){
                $message = $this->messageRepository->getById($button[CustomOptions::FIELD_BUTTON_ACTION]);
                $result [] = ['type' => $this->helper->convertButtonType($button[Button::BUTTON_TYPE]), 'title' => $button[CustomOptions::FIELD_BUTTON_LABEL], $this->helper->checkPayloadOrUrl($button[Button::BUTTON_TYPE]) => $message->getName()];
            }else{
                $result [] = ['type' => $this->helper->convertButtonType($button[Button::BUTTON_TYPE]), 'title' => $button[CustomOptions::FIELD_BUTTON_LABEL], $this->helper->checkPayloadOrUrl($button[Button::BUTTON_TYPE]) => $this->helper->convertSpecialValue($button[CustomOptions::FIELD_BUTTON_ACTION])];
            }
        }
        return $result;
    }
    /**
     * @param $buttons
     * @return array
     */
	public function setupQuickReplyButton($buttons): array
    {
		$result = [];
		if (isset($buttons)) {
		    for($i = 0 ; $i < count($buttons); $i++){
		        $message = $this->messageRepository->getById($buttons[$i][InsertNewMessageData::MESSAGE_ID_PAYLOAD]);
                $result [] = ['content_type' => 'text', 'title' => $message->getName(), 'payload' => $message->getName(), 'image_url' => null];
            }
		}
		return $result;
	}

	/**
	 * @return Quote
	 */
	public function getQuote(): Quote
    {
		return $this->quote;
	}

	/**
	 * @param Quote $quote
	 */
	public function setQuote(Quote $quote)
	{
		$this->quote = $quote;
	}
}
