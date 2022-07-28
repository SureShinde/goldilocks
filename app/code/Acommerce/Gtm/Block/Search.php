<?php
namespace Acommerce\Gtm\Block;

/**
 * Class \Acommerce\Gtm\Block\Search
 */
class Search extends \Acommerce\Gtm\Block\Category
{
    /**
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection|null
     */
    public function getProductCollection()
    {
        $searchResultListBlock = $this->_layout->getBlock('search_result_list');

        if (empty($searchResultListBlock)) {
            return null;
        }

        // Fetch the current collection from the block and set pagination
        $collection = $searchResultListBlock->getLoadedProductCollection();
        $collection->setCurPage($this->getCurrentPage())->setPageSize($this->getLimit());

        return $collection;
    }
}
