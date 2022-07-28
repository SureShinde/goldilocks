<?php

namespace Amasty\Preorder\Helper;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * @deprecated All methods move into models
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const BACKORDERS_PREORDER_OPTION = 101;

    /**
     * @var array
     */
    protected $isOrderProcessing = false;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    private $filterManager;

    /**
     * @var \Amasty\Preorder\Model\Product\RetrieveNote\GetNote
     */
    private $getNote;

    /**
     * @var \Amasty\Preorder\Model\Product\RetrieveNote\GetCartLabel
     */
    private $getCartLabel;

    /**
     * @var \Amasty\Preorder\Model\Product\Detect\IsProductPreorderInterface
     */
    private $isProductPreorder;

    /**
     * @var \Amasty\Preorder\Model\Quote\Item\GetNote
     */
    private $getQuoteNote;

    /**
     * @var \Amasty\Preorder\Model\Quote\Item\IsPreorder
     */
    private $isItemPreorder;

    /**
     * @var \Amasty\Preorder\Model\Order\GetWarning
     */
    private $getPreorderWarning;

    /**
     * @var \Amasty\Preorder\Model\Order\IsPreorder
     */
    private $isOrderPreorder;

    /**
     * @var \Amasty\Preorder\Model\Order\IsItemPreorder
     */
    private $isOrderItemPreorder;

    /**
     * @var \Amasty\Preorder\Model\Order\GetItemNote
     */
    private $getOrderItemNote;

    public function __construct(
        Context $context,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Amasty\Preorder\Model\Product\RetrieveNote\GetNote $getNote,
        \Amasty\Preorder\Model\Product\RetrieveNote\GetCartLabel $getCartLabel,
        \Amasty\Preorder\Model\Product\Detect\IsProductPreorderInterface $isProductPreorder,
        \Amasty\Preorder\Model\Quote\Item\GetNote $getQuoteNote,
        \Amasty\Preorder\Model\Quote\Item\IsPreorder $isItemPreorder,
        \Amasty\Preorder\Model\Order\GetWarning $getPreorderWarning,
        \Amasty\Preorder\Model\Order\IsPreorder $isOrderPreorder,
        \Amasty\Preorder\Model\Order\IsItemPreorder $isOrderItemPreorder,
        \Amasty\Preorder\Model\Order\GetItemNote $getOrderItemNote
    ) {
        parent::__construct($context);
        $this->filterManager = $filterManager;
        $this->getNote = $getNote;
        $this->getCartLabel = $getCartLabel;
        $this->isProductPreorder = $isProductPreorder;
        $this->getQuoteNote = $getQuoteNote;
        $this->isItemPreorder = $isItemPreorder;
        $this->getPreorderWarning = $getPreorderWarning;
        $this->isOrderPreorder = $isOrderPreorder;
        $this->isOrderItemPreorder = $isOrderItemPreorder;
        $this->getOrderItemNote = $getOrderItemNote;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param int $qtyMultiplier
     *
     * @return bool
     *
     * @deprecated
     * @see \Amasty\Preorder\Model\Quote\Item\IsPreorder::execute
     */
    public function getQuoteItemIsPreorder(\Magento\Quote\Model\Quote\Item $item, $qtyMultiplier = 1)
    {
        return $this->isItemPreorder->execute($item, $qtyMultiplier);
    }

    /**
     * @param Product $product
     * @return bool
     *
     * @deprecated
     * @see \Amasty\Preorder\Model\Product\Detect\IsProductPreorder::execute
     */
    public function getIsProductPreorder(Product $product)
    {
        return $this->isProductPreorder->execute($product);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     *
     * @deprecated
     * @see \Amasty\Preorder\Model\Order\IsPreorder::execute
     */
    public function getOrderIsPreorderFlag(\Magento\Sales\Model\Order $order)
    {
        return $this->isOrderPreorder->execute($order);
    }

    /**
     * @param $orderId
     * @return string
     *
     * @deprecated
     * @see \Amasty\Preorder\Model\Order\GetWarning::execute
     */
    public function getOrderPreorderWarning($orderId)
    {
        return $this->getPreorderWarning->execute((int) $orderId);
    }

    /**
     * @param $itemId
     * @return bool
     *
     * @deprecatesd
     * @see \Amasty\Preorder\Model\Order\IsItemPreorder::execute
     */
    public function getOrderItemIsPreorderFlag($itemId)
    {
        return $this->isOrderItemPreorder->execute((int) $itemId);
    }

    /**
     * @param $quoteItem
     * @return null|string|string[]
     *
     * @deprecated
     * @see \Amasty\Preorder\Model\Quote\GetNote::execute
     */
    public function getQuoteItemPreorderNote($quoteItem)
    {
        return $this->getQuoteNote->execute($quoteItem);
    }

    /**
     * @param Product $product
     * @return string
     *
     * @deprecated
     * @see \Amasty\Preorder\Model\Product\RetrieveNote\GetNote::execute
     */
    public function getProductPreorderNote(Product $product)
    {
        return $this->getNote->execute($product);
    }

    /**
     * @return string
     *
     * @deprecated
     */
    public function getPreorderNotePosition()
    {
        return $this->getCurrentStoreConfig('ampreorder/general/note_position');
    }

    /**
     * Wrapper for standard strip_tags() function with extra functionality for html entities
     *
     * @param string $data
     * @param string|null $allowableTags
     * @param bool $allowHtmlEntities
     * @return string
     *
     * @deprecated
     * @see \Amasty\Preorder\Model\Utils\StripTags::execute
     */
    public function stripTags($data, $allowableTags = null, $allowHtmlEntities = false)
    {
        return $this->filterManager->stripTags(
            $data,
            ['allowableTags' => $allowableTags, 'escape' => $allowHtmlEntities]
        );
    }

    /**
     * @param Product $product
     * @return string
     *
     * @deprecated
     * @see \Amasty\Preorder\Model\Product\RetrieveNote\GetCartLabel::execute
     */
    public function getProductPreorderCartLabel(Product $product)
    {
        return $this->getCartLabel->execute($product);
    }

    /**
     * @return string
     */
    public function getDefaultPreorderCartLabel()
    {
        return $this->getCurrentStoreConfig('ampreorder/general/addtocartbuttontext');
    }

    /**
     * @return bool
     */
    public function preordersEnabled()
    {
        return $this->getCurrentStoreConfig('ampreorder/functional/enabled');
    }

    /**
     * @return bool
     */
    public function disableForPositiveQty()
    {
        return (bool)$this->getCurrentStoreConfig('ampreorder/functional/disableforpositiveqty');
    }

    /**
     * @return bool
     */
    public function allowEmpty()
    {
        return (bool)$this->getCurrentStoreConfig('ampreorder/functional/allowemptyqty');
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    protected function getCurrentStoreConfig($path)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @param bool $fromDatabase
     * @return string
     *
     * @deprecated
     * @see \Amasty\Preorder\Model\Order\GetItemNote::execute
     */
    public function getOrderItemPreorderNote(\Magento\Sales\Model\Order\Item $orderItem, $fromDatabase = true)
    {
        return $this->getOrderItemNote->execute($orderItem, $fromDatabase);
    }

    /**
     * @return mixed
     */
    public function isPreOrderNoteShow()
    {
        return $this->getCurrentStoreConfig('ampreorder/general/show_preorder_note');
    }

    /**
     * @return bool
     */
    public function isWarningInEmail()
    {
        return (bool)$this->getCurrentStoreConfig('ampreorder/additional/addwarningtoemail');
    }

    /**
     * @return mixed
     */
    public function getCartMessage()
    {
        return $this->getCurrentStoreConfig('ampreorder/general/cart_message');
    }

    /**
     * @return string
     */
    public function getBelowZeroMessage()
    {
        return $this->getCurrentStoreConfig('ampreorder/general/below_zero_message');
    }
}
