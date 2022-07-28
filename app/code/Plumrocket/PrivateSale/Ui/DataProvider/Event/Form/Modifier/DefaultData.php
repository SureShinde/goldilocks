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
 * @package     Plumrocket_PrivateSale
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Ui\DataProvider\Event\Form\Modifier;

use Magento\Framework\UrlInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class DefaultData implements ModifierInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * DefaultData constructor.
     *
     * @param RequestInterface $request
     * @param ProductRepositoryInterface $productRepository
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        RequestInterface $request,
        ProductRepositoryInterface $productRepository,
        UrlInterface $urlBuilder
    ) {
        $this->request = $request;
        $this->productRepository = $productRepository;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @inheritDoc
     */
    public function modifyData(array $data)
    {
        $data[null]['product_button_label'] = __('Choose Product');
        $data[null]['product_edit_url'] = $this->urlBuilder->getUrl('catalog/product/edit');
        $data[null]['event_type'] = $this->request->getParam('type');
        $data[null]['product_event'] = $this->request->getParam('product_event');
        $data[null]['category_event'] = $this->request->getParam('category_event');

        foreach ($data as & $eventData) {
            if (empty($eventData['product_event'])) {
                $productButtonLabel  = __('Choose Product');
            } else {
                try {
                    $product = $this->productRepository->getById($eventData['product_event']);
                    $eventData['product_label'] = $product->getName() . ' (' . $product->getSku() . ')';
                    $eventData['product_link']
                        = $this->urlBuilder->getUrl('catalog/product/edit', ['id' => $product->getId()]);
                    $productButtonLabel = __('Change Product');
                } catch (NoSuchEntityException $e) {
                    $eventData['product_event'] = null;
                    $productButtonLabel  = __('Choose Product');
                }
            }

            $eventData['product_edit_url'] = $this->urlBuilder->getUrl('catalog/product/edit');
            $eventData['product_button_label'] = $productButtonLabel;
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
