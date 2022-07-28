<?php

namespace Magenest\AbandonedCart\Ui\Component\Listing\Column\BlackList;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class ViewAction extends \Magento\Ui\Component\Listing\Columns\Column
{

    /** @var \Magento\Framework\UrlInterface $_urlBuilder */
    protected $_urlBuilder;

    /**
     * ViewAction constructor.
     *
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->_urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $storeId = $this->context->getFilterParam('store_id');
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')]['edit'] = [
                    'href'   => $this->_urlBuilder->getUrl(
                        'abandonedcart/blacklist/delete',
                        ['id' => $item['id'], 'store' => $storeId]
                    ),
                    'label'  => __('Delete'),
                    'hidden' => false,
                ];
            }
        }
        return $dataSource;
    }
}
