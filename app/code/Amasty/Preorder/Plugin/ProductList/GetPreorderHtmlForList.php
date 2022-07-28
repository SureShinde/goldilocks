<?php

declare(strict_types=1);

namespace Amasty\Preorder\Plugin\ProductList;

use Amasty\Preorder\Model\ConfigProvider;
use Amasty\Preorder\Model\Product\Processor;
use Amasty\Preorder\ViewModel\Product\ProductList\Preorder;
use Amasty\Xsearch\Block\Search\Product as XsearchProduct;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;

class GetPreorderHtmlForList
{
    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Preorder
     */
    private $preorderListViewModel;

    public function __construct(
        Processor $processor,
        ConfigProvider $configProvider,
        Preorder $preorderListViewModel
    ) {
        $this->processor = $processor;
        $this->configProvider = $configProvider;
        $this->preorderListViewModel = $preorderListViewModel;
    }

    /**
     * @param Template $subject
     * @param ProductInterface[] $productItems
     * @return string
     * @throws LocalizedException
     */
    public function get(Template $subject, array $productItems): string
    {
        if (!$this->configProvider->isEnabled() || $this->isSkipXsearchPopup($subject)) {
            return '';
        }

        $this->processor->execute($productItems);
        $preorderListBlock = $subject->getLayout()->getBlock('preorder.list');

        if (!$preorderListBlock) {
            $preorderListBlock = $subject->getLayout()->createBlock(
                \Magento\Framework\View\Element\Template::class,
                'preorder.list',
                ['data' => ['preorder_list_view_model' => $this->preorderListViewModel]]
            )->setTemplate('Amasty_Preorder::product/list/preorder.phtml');
        }

        return $preorderListBlock->setData('items', $productItems)->toHtml();
    }

    /**
     * Xsearch popup processed via
     * @see \Amasty\Preorder\Plugin\Xsearch\Block\Search\Product\ChangeCartLabel
     *
     * @param Template $subject
     * @return bool
     */
    private function isSkipXsearchPopup(Template $subject): bool
    {
        return $subject instanceof XsearchProduct;
    }
}
