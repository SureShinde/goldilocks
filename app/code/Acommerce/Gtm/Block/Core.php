<?php
namespace Acommerce\Gtm\Block;

/**
 * Class \Acommerce\Gtm\Block\Core
 */
class Core extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Acommerce\Gtm\Helper\Data
     */
    protected $helper;

    /**
     * @var \Acommerce\Gtm\Model\Storage
     */
    protected $storage;



    protected  $_routeMapPageName = [
        'catalog/category/view' => 'other',
        'cms/index/index' => 'home',
        'catalog/product/view' => 'offerdetail',
        'checkout/index/index' => 'conversionintent',
        'checkout/cart/index' => 'conversionintent',
        'checkout/onepage/success' => 'conversion',
        'catalogsearch/result/index' => 'searchresults'

    ];

    protected $_pageTitle;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Acommerce\Gtm\Helper\Data $helper
     * @param \Acommerce\Gtm\Model\Storage $storage
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Acommerce\Gtm\Helper\Data $helper,
        \Acommerce\Gtm\Model\Storage $storage,
        \Magento\Framework\View\Page\Title $pageTitle,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    )
    {
        $this->helper = $helper;
        $this->storage = $storage;
//        $this->_isScopePrivate = true;
        $this->_pageTitle = $pageTitle;
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->helper->isEnabled();
    }


    /**
     * @return bool
     */
    public function excludeTaxFromTransaction()
    {
        return $this->helper->excludeTaxFromTransaction();
    }

    /**
     * @return bool
     */
    public function excludeShippingFromTransaction()
    {
        return $this->helper->excludeShippingFromTransaction();
    }


    /**
     * @param $label
     * @param $value
     * @return $this
     */
    public function setEcommerceData($label, $value) {
        $ecommerceData = $this->getEcommerceData();
        if (!$ecommerceData)  {
            $ecommerceData = [];
        }
        $ecommerceData[$label] = $value;

        $this->setDataLayerOption('ecommerce', $ecommerceData);
        return $this;
    }

    /**
     * @param $label
     * @return mixed
     */
    public function getEcommerceData($label = null) {
        $ecommerceData = $this->getDataLayerOption('ecommerce');
        if (isset($label)) {
            return $ecommerceData[$label];
        }

        return $ecommerceData;
    }

    /**
     * @param $label
     * @param $value
     * @return $this
     */
    public function setDataLayerOption($label, $value) {
        $this->storage->setData($label, $value);
        return $this;
    }

    /**
     * @param $label
     * @return mixed
     */
    public function getDataLayerOption($label = null) {
        if ($label) {
            return $this->storage->getData($label);
        }

        return $this->storage->getData();
    }

    /**
     * @return string
     */
    public function getDataLayerAsJson()
    {
        $options = $this->getDataLayerOption();
        $options = $this->_genericOptions($options);
        $options = $this->_splitImpressions($options);
        $options = $this->_removeExceptionKey($options);

        return json_encode($options);
    }

    private function _removeExceptionKey($options){
        if(array_key_exists('ecommerce', $options[0])){

            if(isset($options[0]['ecommerce']['impressions'])){
                $options[0]['listProducts'] = $options[0]['ecommerce']['impressions'];
                unset($options[0]['ecommerce']['impressions']);
            }

            if(isset($options[0]['ecommerce']['detail'])){
                $optionDetail = $options[0]['ecommerce']['detail']['products'][0];

                $options[0]['productId'] = $optionDetail['sku'];
                $options[0]['productName'] = $optionDetail['name'];

                if (isset($optionDetail['brand'])) {
                    $options[0]['brand'] = $optionDetail['brand'];
                }

                $options[0]['productPrice'] = $optionDetail['price'];
                $options[0]['category'] = $optionDetail['category'];

                unset($options[0]['ecommerce']['detail']);

            }

            if(isset($options[0]['ecommerce']['shoppingCart'])) {
                $optionDetail = $options[0]['ecommerce']['shoppingCart'];
                $options[0]['basketID'] = $optionDetail['basketID'];
                $options[0]['basketTotal'] = $optionDetail['basketTotal'];
                $options[0]['basketProducts'] = $optionDetail['basketProducts'];
                $options[0]['actionField'] = $optionDetail['actionField'];

                if (isset($optionDetail['brand'])) {
                    $options[0]['brand'] = $optionDetail['brand'];
                }

                unset( $options[0]['ecommerce']);
            }

            if(isset($options[0]['ecommerce']['checkout'])) {
                $optionDetail = $options[0]['ecommerce']['checkout'];
                $options[0]['basketID'] = $optionDetail['basketID'];
                $options[0]['basketTotal'] = $optionDetail['basketTotal'];
                $options[0]['basketProducts'] = $optionDetail['basketProducts'];
                $options[0]['actionField'] = $optionDetail['actionField'];

                if (isset($optionDetail['brand'])) {
                    $options[0]['brand'] = $optionDetail['brand'];
                }
                unset( $options[0]['ecommerce']);
            }

            if(isset($options[0]['ecommerce']['purchase'])) {
                $optionDetail = $options[0]['ecommerce']['purchase'];
                $options[0]['transactionProducts'] = $optionDetail['products'];
                $options[0]['transactionId'] = $optionDetail['actionField']['transactionId'];
                $options[0]['transactionTotal'] = $optionDetail['actionField']['transactionTotal'];
                $options[0]['couponCode'] = $optionDetail['actionField']['coupon'];
				$options[0]['tax'] = $optionDetail['actionField']['tax'];
                $options[0]['transactionShipping'] = $optionDetail['actionField']['shipping'];
                $options[0]['paymentMethod'] = $optionDetail['actionField']['paymentMethod'];
                $options[0]['shippingMethod'] = $optionDetail['actionField']['shippingMethod'];

                if (isset($optionDetail['brand'])) {
                    $options[0]['brand'] = $optionDetail['brand'];
                }

                unset( $options[0]['ecommerce']);
            }
        }

        return $options;

    }

    /**
     * @return string
     */
    public function getCurrencyCode() {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();
    }


    /**
     * @param $options
     * @return mixed
     */
    private function _splitImpressions($options) {

        $result = [];
        $chunkLimit = $this->helper->getImpressionChunkSize();

        if (isset($options['ecommerce']['impressions'])) {
            $currencyCode = $options['ecommerce']['currencyCode'];
            $originalImpressions = $options['ecommerce']['impressions'];
            $impressionsCount = count($originalImpressions);
            if ($impressionsCount <= $chunkLimit) {
                $result[] = $options;
                return $result;
            }

            $impressionChunks =array_chunk($originalImpressions, $chunkLimit);
            $options['ecommerce']['impressions'] = $impressionChunks[0];
            $result[] = $options;

            $chunkCount = count($impressionChunks);
            for ($i = 1; $i<$chunkCount; $i++ ) {
                $newImpressionChunk = [];
                $newImpressionChunk['ecommerce'] = [];
                $newImpressionChunk['ecommerce']['currencyCode'] = $currencyCode;
                $newImpressionChunk['ecommerce']['impressions'] = $impressionChunks[$i];
                $result[] = $newImpressionChunk;
            }

            return $result;
        } else {
            $result[] = $options;
            return $result;
        }
    }

    private function  _genericOptions($options){

        $options['pageType'] = $this->_getPageNameByRoute();
        $options['deviceType'] = $this->_getDeviceType();
        $options['pageTitle'] = $this->_getPageTitle();
        $options['visitorId'] = $this->_getCustomer();

        return $options;

    }

    /**
     * Return the current page name by route
     *
     * @return pageName
     */

    private function _getPageNameByRoute()
    {
        $pageName = 'other';
        $moduleName = $this->getRequest()->getModuleName();
        $controllerName = $this->getRequest()->getControllerName();
        $actionName = $this->getRequest()->getActionName();
        $routeName = $moduleName.'/'.$controllerName.'/'.$actionName;

        if(!empty($this->_routeMapPageName[$routeName])){
            $pageName = $this->_routeMapPageName[$routeName];
        }
        return $pageName;
    }

    private function _getDeviceType()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $deviceType = 'd';

        if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$userAgent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($userAgent,0,4))){

            $deviceType = 'm';

        }

        return $deviceType;
    }

    private function _getPageTitle()
    {
        $pageTitle = '';
        if($this->_pageTitle->getShort()){
            $pageTitle = $this->_pageTitle->getShort();
        }
        return$pageTitle;
    }

    private function  _getCustomer(){
        $visitorID = '';
        $customer = $this->customerSession->getCustomer();

        if(!empty($customer) && $customer->getId() > 0) {
            $visitorID = $customer->getId();
        }

        return $visitorID;

    }

}
