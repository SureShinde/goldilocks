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

namespace Plumrocket\PrivateSale\Ui\DataProvider\SplashPage\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Plumrocket\PrivateSale\Model\Splashpage;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var Splashpage
     */
    private $splashPage;

    /**
     * @var PoolInterface
     */
    private $pool;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param Splashpage $splashPage
     * @param PoolInterface $pool
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        Splashpage $splashPage,
        PoolInterface $pool,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->splashPage = $splashPage;
        $this->pool = $pool;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $data = $this->prepareData($this->splashPage->getData());
        return [null => $data];
    }

    /**
     * @inheritDoc
     */
    public function addField($field, $alias = null)
    {
        return;
    }

    /**
     * @inheritDoc
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        return;
    }

    /**
     * @param $data
     * @return array
     */
    private function prepareData($data)
    {
        $imageData = [];

        try {
            /** @var \Plumrocket\PrivateSale\Model\SplashPageImage $image */
            foreach ($this->splashPage->getImages() as $image) {
                $tmpData = $image->getData();

                if (isset($tmpData['name'])) {
                    //phpcs:ignore Magento2.Functions.DiscouragedFunction
                    $tmpData['name'] = basename($tmpData['name']);
                }

                $tmpData['image'] = [$image->getImageData()];
                $imageData[] = $tmpData;
            }
        } catch(\Exception $e) {
        }

        $data['images'] = $imageData;

        return $data;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getMeta()
    {
        $this->meta = parent::getMeta();
        $this->pool->getModifiers();

        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $this->meta = $modifier->modifyMeta($this->meta);
        }

        return $this->meta;
    }
}
