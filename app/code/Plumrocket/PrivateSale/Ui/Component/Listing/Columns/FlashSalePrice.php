<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket Private Sales and Flash Sales
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Ui\Component\Listing\Columns;

use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class FlashSalePrice extends Column
{
    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param ScopeResolverInterface $scopeResolver
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ScopeResolverInterface $scopeResolver,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->scopeResolver = $scopeResolver;
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
            $fieldName = $this->getData('name');

            foreach ($dataSource['data']['items'] as & $item) {
                if (! empty($item[$fieldName])) {
                    $item[$fieldName] = number_format((float) $item[$fieldName], 2);
                }
            }
        }

        return $dataSource;
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        $currency = $this->scopeResolver->getScope()->getBaseCurrency();
        $config = $this->getData('config');
        $symbol = $currency->getCurrencySymbol();
        $priceFormat = $currency->getOutputFormat();

        if (strpos($priceFormat, '%s') === 0) {
            $config['beforeSymbol'] = $symbol;
        } else {
            $config['afterSymbol'] = $symbol;
        }

        $this->setData('config', $config);
        parent::prepare();
    }
}
