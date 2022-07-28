<?php

namespace Magenest\Review\Plugin\Block\Html;

class Pager
{
    const LIMIT_REVIEW_SHOWN_ON_PRODUCT_PAGE = 5;

    /**
     * @param \Magento\Theme\Block\Html\Pager $subject
     * @param $result
     * @return int
     */
    public function afterGetLimit(\Magento\Theme\Block\Html\Pager $subject, $result): int
    {
        if ($subject->getNameInLayout() === 'product_review_list.toolbar') {
            return self::LIMIT_REVIEW_SHOWN_ON_PRODUCT_PAGE;
        }
        return $result;
    }
}
