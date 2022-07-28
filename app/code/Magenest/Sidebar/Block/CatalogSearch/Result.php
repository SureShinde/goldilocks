<?php

namespace Magenest\Sidebar\Block\CatalogSearch;

class Result extends \Magento\CatalogSearch\Block\Result
{
    protected function _prepareLayout()
    {
        $title = $this->getSearchQueryText();
        $this->pageConfig->getTitle()->set($title);
        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setTemplate("Magenest_Sidebar::html/title.phtml");
            $pageMainTitle->setPageTitle($title);
        }
        // add Home breadcrumb
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbs) {
            $breadcrumbs->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            )->addCrumb(
                'search',
                ['label' => $title, 'title' => $title]
            );
        }

        return \Magento\Framework\View\Element\Template::_prepareLayout();
    }
}
