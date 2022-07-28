<?php

namespace Amasty\StorePickupWithLocator\Block;

class Pager extends \Magento\Theme\Block\Html\Pager
{
    /**
     * Return correct URL for StorePickupWithLocator ajax request
     *
     * @param array $params
     * @return string
     */
    public function getPagerUrl($params = [])
    {
        if ($query = $this->getRequest()->getParam('query')) {
            $params['query'] = $query;
        }

        return $this->_urlBuilder->getUrl('amstorepickup/map/update', $params);
    }
}
