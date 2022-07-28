<?php

namespace Magenest\StoreLocatorPopup\Controller\Ajax;

use Amasty\Storelocator\Api\Data\LocationInterface;
use Amasty\Storelocator\Block\Adminhtml\Location\Edit\Form\Status;
use Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Serialize\Serializer\Json as JsonFramework;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreSwitcher\ContextInterfaceFactory;
use Magento\Store\Model\StoreSwitcher\RedirectDataGenerator;

class SelectStore extends Action
{
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /** @var CollectionFactory  */
    protected $locationCollectionFactory;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /** @var StoreManagerInterface  */
    protected $storeManager;

    /**
     * @var RedirectDataGenerator
     */
    protected $redirectDataGenerator;

    /**
     * @var ContextInterfaceFactory
     */
    protected $contextFactory;

    /** @var UrlInterface  */
    protected $urlBuilder;

    /** @var JsonFramework  */
    protected $jsonFramework;

    /**
     * @param Context $context
     * @param \Magento\Checkout\Model\Cart $cart
     * @param CollectionFactory $locationCollectionFactory
     * @param StoreRepositoryInterface $storeRepository
     * @param StoreManagerInterface $storeManager
     * @param RedirectDataGenerator $redirectDataGenerator
     * @param ContextInterfaceFactory $contextFactory
     * @param UrlInterface $urlBuilder
     * @param JsonFramework $jsonFramework
     */
    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Cart $cart,
        CollectionFactory $locationCollectionFactory,
        StoreRepositoryInterface $storeRepository,
        StoreManagerInterface $storeManager,
        RedirectDataGenerator $redirectDataGenerator,
        ContextInterfaceFactory $contextFactory,
        UrlInterface $urlBuilder,
        JsonFramework $jsonFramework
    ) {
        parent::__construct($context);
        $this->cart = $cart;
        $this->locationCollectionFactory = $locationCollectionFactory;
        $this->storeRepository = $storeRepository;
        $this->storeManager = $storeManager;
        $this->redirectDataGenerator = $redirectDataGenerator;
        $this->contextFactory = $contextFactory;
        $this->urlBuilder = $urlBuilder;
        $this->jsonFramework = $jsonFramework;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $redirectUrl = $this->urlBuilder->getUrl();
        try {
            $params = $this->getRequest()->getParams();
            if (isset($params['storeId'])) {
                $locatorStoreId = $params['storeId'];
                $locationCollection = $this->locationCollectionFactory->create()
                    ->addFieldToFilter('status', Status::ENABLED)
                    ->addFieldToFilter(LocationInterface::ID, $locatorStoreId)
                    ->setPageSize(1)
                    ->setCurPage(1)
                    ->getFirstItem();
                $storeIds = $locationCollection->getStoreIds();
                if (!empty($storeIds)) {
                    $storeId = reset($storeIds);
                    $targetStore = $this->storeRepository->getById($storeId);
                    $fromStore = $this->storeManager->getStore();
                    $redirectUrl = $this->getRedirectStore($fromStore, $targetStore);
                    if ($fromStore->getId() != $targetStore->getId()) {
                        $this->cart->truncate()->save();
                    }
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        } catch (\Exception $exception) {
            $this->messageManager->addExceptionMessage($exception, __('We can\'t update the shopping cart.'));
        }
        $response = [
            'redirect_url' => $redirectUrl
        ];

        $this->getResponse()->setBody($this->jsonFramework->serialize($response));
    }

    /**
     * @param $fromStore
     * @param $targetStore
     * @return string
     */
    protected function getRedirectStore($fromStore, $targetStore)
    {
        $redirectData = $this->redirectDataGenerator->generate(
            $this->contextFactory->create(
                [
                    'fromStore' => $fromStore,
                    'targetStore' => $targetStore,
                    'redirectUrl' => $this->_redirect->getRedirectUrl()
                ]
            )
        );
        $encodedUrl = $this->_request->getParam(ActionInterface::PARAM_NAME_URL_ENCODED);
        $query = [
            '___from_store' => $fromStore->getCode(),
            StoreResolverInterface::PARAM_NAME => $targetStore->getCode(),
            ActionInterface::PARAM_NAME_URL_ENCODED => $encodedUrl,
            'data' => $redirectData->getData(),
            'time_stamp' => $redirectData->getTimestamp(),
            'signature' => $redirectData->getSignature(),
        ];
        $arguments = [
            '_nosid' => true,
            '_query' => $query
        ];
        return $this->urlBuilder->getUrl('stores/store/switch', $arguments);
    }
}
