<?php
namespace Magenest\CustomPreorder\Plugin\Block\Component;

use Amasty\DeliveryDateManager\Block\Component\CalendarComponent;
use Amasty\Preorder\Model\Product\Detect\IsProductPreorder as PreorderData;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class CalendarComponentPlugin
{
    const PRE_ORDER_MAX_LEAD_TIME = 14;

    /** @var PreorderData  */
    protected $preorderData;

    /** @var CheckoutSession  */
    protected $checkoutSession;

    /** @var ScopeConfigInterface  */
    protected $scopeConfig;

    /**
     * @param PreorderData $preorderData
     * @param CheckoutSession $checkoutSession
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        PreorderData $preorderData,
        CheckoutSession $checkoutSession,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->preorderData = $preorderData;
        $this->checkoutSession = $checkoutSession;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterGetComponent(
        CalendarComponent $subject,
        $result,
        int $storeId
    ) {
        $quote = $this->checkoutSession->getQuote();
        $isPreorderProduct = false;
        foreach ($quote->getItems() as $item) {
            $isPreorderProduct = $this->preorderData->execute($item->getProduct());
            if ($isPreorderProduct) {
                break;
            }
        }
        $result['config']['options']['maxDate'] = '+' . self::PRE_ORDER_MAX_LEAD_TIME . 'd';
        if ($isPreorderProduct) {
            $minDate = $this->scopeConfig->getValue(
                'ampreorder/functional/minimun_delivery_time',
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
            if ($minDate == null || $minDate < 0) {
                $minDate = 0;
            }
            $result['config']['options']['minDate'] = '+' . $minDate . 'd';
        }
        return $result;
    }
}
