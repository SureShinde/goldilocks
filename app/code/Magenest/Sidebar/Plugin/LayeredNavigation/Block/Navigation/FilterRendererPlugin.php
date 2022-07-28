<?php

namespace Magenest\Sidebar\Plugin\LayeredNavigation\Block\Navigation;

use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\LayeredNavigation\Block\Navigation\FilterRenderer;

class FilterRendererPlugin
{
    /** @var LayoutInterface  */
    protected $layout;

    /**
     * Path to RenderLayered Block
     *
     * @var string
     */
    protected $block = \Magenest\Sidebar\Block\LayeredNavigation\RangeFilter::class;

    /**
     * @param LayoutInterface $layout
     */
    public function __construct(
        LayoutInterface $layout
    ) {
        $this->layout = $layout;
    }

    /**
     * @param FilterRenderer $subject
     * @param $result
     * @param FilterInterface $filter
     *
     * @return mixed
     */
    public function afterRender(
        FilterRenderer $subject,
        $result,
        FilterInterface $filter
    ) {
        if ($filter->getRequestVar() == "price") {
            return $this->layout
                ->createBlock($this->block)
                ->setPriceFilter($filter)
                ->toHtml();
        }
        return $result;
    }
}
