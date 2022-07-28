<?php

declare(strict_types=1);

namespace Amasty\PreOrderAnalytic\Ui\Component\Listing\Column;

use Amasty\PreOrderAnalytic\Model\ResourceModel\LoadBestsellerPreorders;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\Store;
use Magento\Ui\Component\Listing\Columns\Column;

class ProductName extends Column
{
    /**
     * @var ProductResource
     */
    private $productResource;

    public function __construct(
        ProductResource $productResource,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->productResource = $productResource;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $currentProductName = $this->productResource->getAttributeRawValue(
                    $item[LoadBestsellerPreorders::PRODUCT_ID_COLUMN],
                    'name',
                    Store::DEFAULT_STORE_ID
                );
                if ($currentProductName) {
                    $item[$this->getData('name')] = $currentProductName;
                }
                unset($item[LoadBestsellerPreorders::PRODUCT_ID_COLUMN]);
            }
        }

        return $dataSource;
    }
}
