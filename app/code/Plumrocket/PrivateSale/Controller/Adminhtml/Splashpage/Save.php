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
 * @package     Plumrocket Private Sales and Flash Sales v4.x.x
 * @copyright   Copyright (c) 2016 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\PrivateSale\Controller\Adminhtml\Splashpage;

use Magento\Backend\App\Action\Context;
use Plumrocket\PrivateSale\Helper\Data;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\Store;
use Plumrocket\PrivateSale\Model\SplashPageImageFactory;
use Plumrocket\PrivateSale\Model\ResourceModel\SplashPageImage\CollectionFactory;
use Plumrocket\PrivateSale\Model\Splashpage;

class Save extends \Plumrocket\PrivateSale\Controller\Adminhtml\Splashpage
{
    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var CollectionFactory
     */
    private $imageCollectionFactory;

    /**
     * Save constructor.
     *
     * @param SplashPageImageFactory $imageFactory
     * @param Splashpage $splashpage
     * @param Config $config
     * @param Data $dataHelper
     * @param Context $context
     * @param SerializerInterface $serializer
     * @param ReinitableConfigInterface $appConfig
     * @param Registry $coreRegistry
     * @param CollectionFactory $imageCollectionFactory
     */
    public function __construct(
        SplashPageImageFactory $imageFactory,
        Splashpage $splashpage,
        Config $config,
        Data $dataHelper,
        Context $context,
        SerializerInterface $serializer,
        ReinitableConfigInterface $appConfig,
        Registry $coreRegistry,
        CollectionFactory $imageCollectionFactory
    ) {
        parent::__construct($imageFactory, $splashpage, $config, $context, $serializer, $appConfig, $coreRegistry);
        $this->imageCollectionFactory = $imageCollectionFactory;
        $this->dataHelper = $dataHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $storeId = $this->getRequest()->getParam('store');
            $data = $this->getRequest()->getPost();
            $params = $storeId ? ['store' => $storeId] : [];
            $videos = $data['videos'] ?? [];
            $images = $this->getRequest()->getPost('images', []);
            unset($data['form_key']);

            if (isset($data['images'])) {
                unset($data['images']);
            }

            if ($storeId) {
                $scope = 'stores';
            } else {
                $scope = 'default';
                $storeId = Store::DEFAULT_STORE_ID;
            }

            if ($videos) {
                foreach ($videos as $key => &$video) {
                    if (empty($video['url'])) {
                        unset($videos[$key]);
                    }
                }

                $data['videos'] = $videos;
            }

            $data = $this->serializer->serialize($data);
            $this->config->saveConfig(Splashpage::CONFIG_PATH, $data, $scope, $storeId);

            $images = array_filter($images, function ($imageData) {
                return ! empty($imageData['image']);
            });

            $newImageIds = array_column($images, 'image_id');
            /** @var \Plumrocket\PrivateSale\Model\ResourceModel\SplashPageImage\Collection $imageCollection */
            $imageCollection = $this->imageCollectionFactory->create();
            $imageIds = $imageCollection->getAllIds();
            $imageIdsToRemove = array_diff($imageIds, $newImageIds);

            if (! empty($imageIdsToRemove)) {
                $imageCollection->addFieldToFilter('image_id', ['in' => $imageIdsToRemove]);
                $imageCollection->walk('delete');
            }

            foreach ($images as $image) {
                if (isset($image['image']) && is_array($image['image'])) {
                    /** @var \Plumrocket\PrivateSale\Model\SplashPageImage $imageModel */
                    $imageModel = $this->imageFactory->create();
                    $currentImage = current($image['image']);
                    $imageModel->loadImage($image['image']);

                    $imageModel->setName($currentImage)
                        ->setImageId($image['image_id'] ?: null)
                        ->setActiveFrom($image['active_from'])
                        ->setActiveTo($image['active_to'])
                        ->setExclude($image['exclude'])
                        ->save();
                }
            }

            $this->appConfig->reinit();
            $this->messageManager->addSuccessMessage(__('You saved the configuration.'));
            $this->_redirect('*/*', $params);
        } catch (\Exception $e) {
            $this->_redirect('*/*', $params);
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }
}
