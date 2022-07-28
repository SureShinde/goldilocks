<?php
namespace Magenest\Sidebar\Block\LayeredNavigation;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Directory\Model\Currency;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json as JsonFramework;
use Magento\Framework\View\Element\Template;

class RangeFilter extends Template
{
    /** @var string  */
    protected $_template = "Magenest_Sidebar::layered_navigation/price-range.phtml";

    /** @var CollectionFactory  */
    protected $_productCollectionFactory;

    /** @var Registry  */
    private $registry;

    /** @var  */
    private $productCollection;

    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /** @var Currency  */
    protected $currency;

    /** @var PriceCurrencyInterface  */
    protected $priceCurrency;

    /** @var JsonFramework  */
    protected $jsonFramework;

    /**
     * @param PriceCurrencyInterface $priceCurrency
     * @param Currency $currency
     * @param CollectionFactory $productFactory
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Data $priceHelper
     * @param JsonFramework $jsonFramework
     * @param Template\Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        Currency $currency,
        CollectionFactory $productFactory,
        CategoryRepositoryInterface $categoryRepository,
        Data $priceHelper,
        JsonFramework $jsonFramework,
        Template\Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->priceHelper = $priceHelper;
        $this->priceCurrency = $priceCurrency;
        $this->currency = $currency;
        $this->registry = $registry;
        $this->_productCollectionFactory = $productFactory;
        $this->categoryRepository = $categoryRepository;
        $this->jsonFramework = $jsonFramework;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl();
    }

    /**
     * @return mixed
     */
    public function getPriceStepConfig()
    {
        return $this->_scopeConfig->getValue("magenest_sidebar/buyascategory/pricestep");
    }

    /**
     * @return \Magento\Catalog\Api\Data\CategoryInterface|mixed|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentCategory()
    {
        $current = $this->registry->registry("current_category");
        if (!$current) {
            $current = $this->categoryRepository->get(2);
        }
        return $current;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getJsonConfig(): string
    {
        $currentFilter = $this->getRequest()->getParam("price");
        if ($currentFilter != null) {
            $currentFilter = explode("-", $currentFilter);
            if (count($currentFilter) >= 2) {
                $from = $currentFilter[0];
                $to = $currentFilter[1];
            }
        } else {
            $from = $this->getProductCollection()->getMinPrice();
            $to = $this->getProductCollection()->getMaxPrice();
        }
        $data = [
            'price_from' => $this->getProductCollection()->getMinPrice(),
            'price_to' => $this->getProductCollection()->getMaxPrice(),
            'step' => $this->getPriceStepConfig(),
            'current_values' => [$from, $to]
        ];

        return $this->jsonFramework->serialize($data);
    }

    /**
     * @return float
     * @throws \Exception
     */
    public function getMinPriceOfCurrentCategory()
    {
        return $this->getProductCollection()->getMinPrice();
    }

    /**
     * @return float
     * @throws \Exception
     */
    public function getMaxPriceOfCurrentCategory()
    {
        return $this->getProductCollection()->getMaxPrice();
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @throws \Exception
     */
    protected function getProductCollection()
    {
        if (!$this->productCollection) {
            $this->productCollection = $this->_productCollectionFactory->create()
                ->addCategoryFilter($this->getCurrentCategory())
                ->addAttributeToSelect('price')
                ->setOrder('price', 'DESC');
            $this->productCollection->load();
        }
        return $this->productCollection;
    }

    /**
     * @return string
     */
    public function getCurrentCurrencySymbol()
    {
        return $this->priceCurrency->getCurrencySymbol();
    }

    /**
     * @param $amount
     *
     * @return string
     */
    public function getFormatedPrice($amount)
    {
        return $this->priceCurrency->convertAndFormat($amount, false);
    }
}
