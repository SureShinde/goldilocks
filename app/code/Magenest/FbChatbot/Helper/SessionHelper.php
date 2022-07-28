<?php

namespace Magenest\FbChatbot\Helper;

use Magenest\FbChatbot\Model\Bot;
use Magento\Catalog\Model\ProductRepository;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\QuoteRepository;
use \Magento\Wishlist\Model\WishlistFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollection;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollection;
use Magento\Customer\Model\CustomerFactory;

class SessionHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
	/**
	 * @var CartManagementInterface
	 */
	protected $quoteManagement;

	/**
	 * @var QuoteIdMaskFactory
	 */
	protected $quoteIdMaskFactory;

	/**
	 * @var CartRepositoryInterface
	 */
	protected $cartRepository;

	/**
	 * @var QuoteRepository
	 */
	protected $quoteRepository;

	/**
	 * @var ProductRepository
	 */
	protected $productRepository;

	/**
	 * @var ResourceConnection
	 */
	protected $resource;

    /**
     * @var WishlistFactory
     */
    protected $wishlistFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var OrderCollection
     */
    protected  $_orderCollectionFactory;

    /**
     * @var QuoteCollection
     */
    protected $quoteCollection;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * SessionHelper constructor.
     *
     * @param Context $context
     * @param CartManagementInterface $quoteManagement
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param CartRepositoryInterface $cartRepository
     * @param QuoteRepository $quoteRepository
     * @param ResourceConnection $resource
     * @param ProductRepository $productRepository
     * @param WishlistFactory $wishlistFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param OrderCollection $orderCollectionFactory
     * @param QuoteCollection $quoteCollection
     * @param Data $helper
     * @param CustomerFactory $customerFactory
     */
	public function __construct(
		Context $context,
		CartManagementInterface $quoteManagement,
		QuoteIdMaskFactory $quoteIdMaskFactory,
		CartRepositoryInterface $cartRepository,
		QuoteRepository $quoteRepository,
		ResourceConnection $resource,
		ProductRepository $productRepository,
        WishlistFactory $wishlistFactory,
        CustomerRepositoryInterface $customerRepository,
        OrderCollection $orderCollectionFactory,
        QuoteCollection $quoteCollection,
        Data $helper,
        CustomerFactory $customerFactory
	) {
		$this->quoteManagement    = $quoteManagement;
		$this->quoteIdMaskFactory = $quoteIdMaskFactory;
		$this->cartRepository     = $cartRepository;
		$this->quoteRepository    = $quoteRepository;
		$this->productRepository  = $productRepository;
		$this->resource           = $resource;
        $this->wishlistFactory    = $wishlistFactory;
        $this->customerRepository = $customerRepository;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->quoteCollection    = $quoteCollection;
        $this->helper             = $helper;
        $this->customerFactory    = $customerFactory;
		parent::__construct($context);
	}

    /**
     * @param $recipientId
     * @return int
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
	public function createEmptyCart($recipientId): int
    {
		/** @var $quoteIdMask QuoteIdMask */
		$quoteIdMask = $this->quoteIdMaskFactory->create();
		$quoteIdMask->setMaskedId($recipientId . "_FB_" . time());

		$cartId = $this->quoteManagement->createEmptyCart();
        $quote = $this->quoteRepository->get($cartId)->setSenderId($recipientId);
        $this->quoteRepository->save($quote);
		$quoteIdMask->setQuoteId($cartId)->save();
		return $cartId;
	}

    /**
     * @param $recipientId
     * @return int|string|null
     */
	public function getLatestActiveQuote($recipientId)
	{
        $connection = $this->quoteCollection->create()->getConnection();
        $quoteIdMask = $this->quoteCollection->create()->getTable('quote_id_mask');
        $select = $this->quoteCollection->create()->getSelect();
        $select->joinLeft(["qim" => $quoteIdMask],"main_table.entity_id = qim.quote_id",["main_table.entity_id"])
            ->where("main_table.is_active = 1 AND qim.masked_id LIKE '{$recipientId}_FB_%'");
        $result = $connection->fetchOne($select);
        return $result ?: null;
	}

    /**
     * @param $recipientId
     * @return CartInterface
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
	public function getRecipientQuote($recipientId): CartInterface
    {
		$quoteId = $this->getLatestActiveQuote($recipientId);
		if ($quoteId == null) {
			$quoteId = $this->createEmptyCart($recipientId);
		}
		return $this->cartRepository->get($quoteId);
	}

    /**
     * @param Quote $quote
     */
	public function saveQuote(Quote $quote)
	{
		$this->quoteRepository->save($quote);
	}
	public function getQuote($quoteId) {
	    return $this->quoteRepository->getActive($quoteId);
    }

    /**
     * @param $productOptions
     * @param Quote $quote
     * @return string|null
     */
	public function addProductToCart($productOptions, Quote $quote): ?string
    {
        try {
            $product = $this->productRepository->getById($productOptions['id']);
            $options = $this->getProductOptions($productOptions);
            if(!empty($options)){
                $requestInfo = new \Magento\Framework\DataObject(
                    ['qty' => 1,'super_attribute' => $options]
                );
                $quote->addProduct($product, $requestInfo);
            }else{
                $quote->addProduct($product, 1);
            }
            $this->saveQuote($quote);
            return $product->getName();
        } catch (\Throwable $e) {
            $this->helper->getLogger()->error("Add product to cart error: " . $e->getMessage());
            return null;
        }
	}

    /**
     * @param $productOptions
     * @return array
     */
	private function getProductOptions($productOptions): array
    {
	    $options = [];
	    if (isset($productOptions[Bot::PRODUCTION_TYPE_PAYLOAD])){
	        switch ($productOptions[Bot::PRODUCTION_TYPE_PAYLOAD]){
                case Configurable::TYPE_CODE:
                    foreach ($productOptions as $key => $val) {
                        if (is_int($key)) {
                            $options[$key] = $val;
                        }
                    }
                    break;
                default:
                    break;
            }
        }
        return $options;
    }

    /**
     * @param $senderId
     * @return array
     */
    public function getQuotesFromBot($senderId): array
    {
	    $quotesId = [];
        $collection = $this->quoteCollection->create()->addFieldToSelect(
            'entity_id'
        )->addFieldToFilter(
            'is_active',
            '0'
        )->addFieldToFilter(
            'sender_id',
            $senderId
        )->setOrder('entity_id')->setPageSize(5);;
        foreach ($collection->getItems() as $quote) {
            $quotesId[] = $quote['entity_id'];
        }

        return $quotesId;
    }

    /**
     * @param $senderId
     * @return array
     */
    public function getOrderFromBot($senderId): array
    {
        $quotesId  = $this->getQuotesFromBot($senderId);
        $collection = $this->_orderCollectionFactory->create()->addFieldToSelect(
            '*'
        )->addFieldToFilter(
            'quote_id',
            ['in' => $quotesId]
        )->setOrder('entity_id')->setPageSize(5);

        return $collection->getItems();
    }

    /**
     * @param $email
     * @return array|string
     */
    public function getWishlist($email) {
        try {
            $customerId = $this->customerRepository->get($email)->getId();
            $wishlist_collection = $this->wishlistFactory->create()->loadByCustomerId($customerId, false)->getItemCollection()->setOrder('wishlist_item_id')->setPageSize(5)->getData();
            return $wishlist_collection;
        } catch (\Throwable $e) {
            return  'notRegistered';
        }

    }

    /**
     * get customer by email, create new if not exist
     * @param $email
     * @param $shippingAddress
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getCustomerByEmail($email, $shippingAddress) {
        $store= $this->helper->getStoreManage()->getStore();
        $websiteId = $this->helper->getStoreManage()->getStore()->getWebsiteId();
        $customer=$this->customerFactory->create();
        $customer->setWebsiteId($websiteId);
        $customer->loadByEmail($email);// load customer by email address
        if(!$customer->getEntityId()){
            //If not available then create this customer
            $customer->setWebsiteId($websiteId)
                ->setStore($store)
                ->setFirstname($shippingAddress->getFirstName())
                ->setLastname($shippingAddress->getLastName())
                ->setEmail($email)
                ->setPassword($email);
            $customer->save();
        }
        return $this->customerRepository->getById($customer->getEntityId());
    }
}
